<?php
namespace App\Controllers;
class DbTest extends BaseController {
    public function index() {
        $m = new \App\Models\DiscountRuleModel();
        $db = \Config\Database::connect();
        $q = $db->table('discount_rules')
            ->where('is_active', 1)
            ->groupStart()
                ->where('discount_type', 'subtotal')
                ->orWhere('discount_type IS NULL')
            ->groupEnd()
            ->where('min_amount <=', 590000)
            ->groupStart()
                ->where('max_amount >=', 590000)
                ->orWhere('max_amount IS NULL')
                ->orWhere('max_amount', 0)
            ->groupEnd()
            ->get()
            ->getResultArray();

        $res = [];
        foreach ($q as $discount) {
            $valid = $m->isDiscountValid($discount, '08/08/2026 16:06', true);
            $res[] = ['id' => $discount['discount_id'], 'valid' => $valid];
        }
        return $this->response->setJSON(['q' => $q, 'validity' => $res]);
    }
}