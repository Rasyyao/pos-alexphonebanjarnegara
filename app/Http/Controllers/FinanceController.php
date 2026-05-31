<?php
namespace App\Http\Controllers;

use App\Services\FinanceService;

class FinanceController extends Controller
{
    public function __construct(private readonly FinanceService $finance) {}

    public function index()
    {
        return view('finance.index', $this->finance->summary());
    }

    public function export()
    {
        return view('finance.export');
    }
}
