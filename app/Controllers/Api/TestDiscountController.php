<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\DiscountRuleModel;

class TestDiscountController extends BaseController
{
    public function index()
    {
        $m = new DiscountRuleModel();
        $res = $m->getApplicableDiscount(685000, '08/08/2026 10:54');
        return $this->response->setJSON(['result' => $res]);
    }
}
