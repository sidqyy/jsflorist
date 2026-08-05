<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberGameModel;
use App\Models\MemberGameLogModel;
use App\Models\MemberPointModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class MemberGameController extends BaseController
{
    protected MemberModel $memberModel;
    protected MemberGameModel $memberGameModel;
    protected MemberGameLogModel $memberGameLogModel;
    protected MemberPointModel $memberPointModel;
    protected $db;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->memberGameModel = new MemberGameModel();
        $this->memberGameLogModel = new MemberGameLogModel();
        $this->memberPointModel = new MemberPointModel();
        $this->db = Database::connect();
    }

    public function award(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $memberId = (int) ($payload['member_id'] ?? 0);
        $gameId = (int) ($payload['game_id'] ?? 0);
        $score = $payload['score'] ?? null;
        $result = $payload['result'] ?? null;
        $metadata = $payload['metadata'] ?? null;

        if ($memberId <= 0 || $gameId <= 0) {
            return $this->respondError('member_id dan game_id wajib diisi', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->respondError('Member tidak ditemukan', ResponseInterface::HTTP_NOT_FOUND);
        }

        if (isset($member['status']) && (int) $member['status'] !== 1) {
            return $this->respondError('Member tidak aktif', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $game = $this->memberGameModel
            ->where('id', $gameId)
            ->where('is_active', 1)
            ->first();

        if (!$game) {
            return $this->respondError('Game tidak ditemukan atau tidak aktif', ResponseInterface::HTTP_NOT_FOUND);
        }

        $dailyLimit = (int) ($game['daily_limit'] ?? 0);
        $playedToday = $this->countTodayPlays($memberId, $gameId);

        if ($dailyLimit > 0 && $playedToday >= $dailyLimit) {
            return $this->respondError('Daily limit tercapai', ResponseInterface::HTTP_TOO_MANY_REQUESTS, [
                'played_today' => $playedToday,
                'daily_limit' => $dailyLimit,
            ]);
        }

        $points = $this->calculatePoints($game, $score, $result);
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();
        try {
            $this->memberGameLogModel->insert([
                'member_id' => $memberId,
                'game_id' => $gameId,
                'points_awarded' => $points,
                'result' => is_string($result) ? $result : (is_numeric($score) ? (string) $score : 'played'),
                'played_at' => $now,
                'metadata' => $this->normalizeMetadata($metadata),
            ]);

            $newBalance = (int) $member['points_balance'];
            $newTotalEarned = (int) ($member['total_points_earned'] ?? 0);

            if ($points > 0) {
                $this->memberPointModel->insert([
                    'member_id' => $memberId,
                    'points' => $points,
                    'type' => 'earn',
                    'source' => 'game',
                    'reference_id' => 'game:' . $gameId,
                    'note' => 'Reward game',
                    'created_at' => $now,
                ]);

                $newBalance += $points;
                $newTotalEarned += $points;

                $this->memberModel->update($memberId, [
                    'points_balance' => $newBalance,
                    'total_points_earned' => $newTotalEarned,
                    'updated_at' => $now,
                ]);
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return $this->respondError('Gagal memberi poin', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondSuccess('Poin berhasil diberikan', [
            'poin_didapat' => $points,
            'saldo_poin_baru' => $newBalance,
        ]);
    }

    public function status(): ResponseInterface
    {
        $memberId = (int) ($this->request->getGet('member_id') ?? 0);
        $gameId = (int) ($this->request->getGet('game_id') ?? 0);

        if ($memberId <= 0 || $gameId <= 0) {
            return $this->respondError('member_id dan game_id wajib diisi', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->respondError('Member tidak ditemukan', ResponseInterface::HTTP_NOT_FOUND);
        }

        if (isset($member['status']) && (int) $member['status'] !== 1) {
            return $this->respondError('Member tidak aktif', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $game = $this->memberGameModel
            ->where('id', $gameId)
            ->where('is_active', 1)
            ->first();

        if (!$game) {
            return $this->respondError('Game tidak ditemukan atau tidak aktif', ResponseInterface::HTTP_NOT_FOUND);
        }

        $dailyLimit = (int) ($game['daily_limit'] ?? 0);
        $playedToday = $this->countTodayPlays($memberId, $gameId);

        $remaining = $dailyLimit > 0 ? max($dailyLimit - $playedToday, 0) : null;
        $canEarn = $dailyLimit === 0 || $remaining > 0;

        return $this->respondSuccess('Status limit harian', [
            'played_today' => $playedToday,
            'remaining_quota' => $remaining,
            'can_earn' => $canEarn,
        ]);
    }

    protected function countTodayPlays(int $memberId, int $gameId): int
    {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        return (int) $this->memberGameLogModel
            ->where('member_id', $memberId)
            ->where('game_id', $gameId)
            ->where('played_at >=', $todayStart)
            ->where('played_at <=', $todayEnd)
            ->countAllResults();
    }

    protected function calculatePoints(array $game, $score, $result): int
    {
        $min = (int) ($game['points_min'] ?? 0);
        $max = (int) ($game['points_max'] ?? 0);
        if ($max < $min) {
            $max = $min;
        }

        if (is_numeric($score)) {
            $scoreVal = (float) $score;
            $ratio = $scoreVal;
            if ($scoreVal > 1) {
                $ratio = $scoreVal / 100;
            }
            $ratio = max(0, min(1, $ratio));
            return (int) round($min + ($max - $min) * $ratio);
        }

        if (is_string($result)) {
            $normalized = strtolower(trim($result));
            if (in_array($normalized, ['win', 'success', 'passed', 'clear'], true)) {
                return $max;
            }
        }

        return $min;
    }

    protected function normalizeMetadata($metadata): ?string
    {
        if ($metadata === null || $metadata === '') {
            return null;
        }

        if (is_array($metadata) || is_object($metadata)) {
            return json_encode($metadata);
        }

        return (string) $metadata;
    }

    protected function getJsonPayload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        return $this->request->getPost() ?? [];
    }

    protected function respondSuccess(string $message, array $data = [], int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ]);
    }

    protected function respondError(string $message, int $statusCode = 400, ?array $data = null): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'error',
                'message' => $message,
                'data' => $data,
            ]);
    }
}
