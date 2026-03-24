<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', self::PER_PAGE_OPTIONS[0]);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $transactions = Transaction::query()
            ->search($request->search)
            ->applyFilters($request->only(['business', 'category', 'transaction_type', 'source', 'status']))
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();

        $filters = Transaction::filterOptions();
        $perPageOptions = self::PER_PAGE_OPTIONS;

        return view('transactions.index', compact('transactions', 'filters', 'perPage', 'perPageOptions'));
    }
}
