@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Transactions</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $transactions->total() }} {{ Str::plural('transaction', $transactions->total()) }} found
            </p>
        </div>
        <a href="{{ route('upload.create') }}"
           class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-500 hover:shadow-md sm:mt-0">
            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Import CSV
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('transactions.index') }}" class="mt-6 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-900/5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            {{-- Search --}}
            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-2">
                <label for="search" class="block text-xs font-medium text-gray-700">Search</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search description or business..."
                           class="block w-full rounded-md border border-gray-300 py-2 pl-10 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- Business --}}
            <div>
                <label for="business" class="block text-xs font-medium text-gray-700">Business</label>
                <select name="business" id="business"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($filters['business'] as $b)
                        <option value="{{ $b }}" {{ request('business') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Category --}}
            <div>
                <label for="category" class="block text-xs font-medium text-gray-700">Category</label>
                <select name="category" id="category"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($filters['category'] as $c)
                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Transaction Type --}}
            <div>
                <label for="transaction_type" class="block text-xs font-medium text-gray-700">Type</label>
                <select name="transaction_type" id="transaction_type"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($filters['transaction_type'] as $t)
                        <option value="{{ $t }}" {{ request('transaction_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Source --}}
            <div>
                <label for="source" class="block text-xs font-medium text-gray-700">Source</label>
                <select name="source" id="source"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($filters['source'] as $s)
                        <option value="{{ $s }}" {{ request('source') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($filters['status'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500">
                    Apply Filters
                </button>
                @if (request()->hasAny(['search', 'business', 'category', 'transaction_type', 'source', 'status']))
                    <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                        Clear all
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <label for="per_page" class="text-xs font-medium text-gray-700">Show</label>
                <select name="per_page" id="per_page"
                        class="rounded-md border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-gray-500">per page</span>
            </div>
        </div>
    </form>

    {{-- Active Filters --}}
    @if (request()->hasAny(['search', 'business', 'category', 'transaction_type', 'source', 'status']))
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-gray-500">Active:</span>
            @foreach (['search', 'business', 'category', 'transaction_type', 'source', 'status'] as $key)
                @if (request()->filled($key))
                    <a href="{{ route('transactions.index', array_merge(request()->except($key), request()->has('per_page') ? ['per_page' => request('per_page')] : [])) }}"
                       class="group inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200 transition-colors hover:bg-indigo-100">
                        {{ str_replace('_', ' ', ucfirst($key)) }}: {{ request($key) }}
                        <svg class="h-3 w-3 text-indigo-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Table --}}
    @if ($transactions->isEmpty())
        <div class="mt-8 rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9.75m3 0h3m-7.5 3H15M2.25 15V6.75A2.25 2.25 0 014.5 4.5h15A2.25 2.25 0 0121.75 6.75v8.25"/>
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">No transactions</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if (request()->hasAny(['search', 'business', 'category', 'transaction_type', 'source', 'status']))
                    No transactions match your filters. Try adjusting or clearing them.
                @else
                    Get started by importing a CSV file.
                @endif
            </p>
            @unless (request()->hasAny(['search', 'business', 'category', 'transaction_type', 'source', 'status']))
                <a href="{{ route('upload.create') }}"
                   class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Import CSV
                </a>
            @endunless
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Business</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Source</th>
                            <th class="sticky top-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($transactions as $txn)
                            <tr class="transition-colors hover:bg-indigo-50/40 even:bg-gray-50/50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                    {{ $txn->date->format('M d, Y') }}
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-sm font-medium text-gray-900" title="{{ $txn->description }}">
                                    {{ $txn->description }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-mono {{ $txn->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $txn->amount >= 0 ? '+' : '-' }}${{ number_format(abs($txn->amount), 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $txn->business }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $txn->category }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    @php
                                        $typeColor = match($txn->transaction_type) {
                                            'Income' => 'bg-green-100 text-green-700 ring-green-600/20',
                                            'Expense' => 'bg-red-100 text-red-700 ring-red-600/20',
                                            'Transfer' => 'bg-blue-100 text-blue-700 ring-blue-600/20',
                                            default => 'bg-gray-100 text-gray-700 ring-gray-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $typeColor }}">
                                        {{ $txn->transaction_type }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $txn->source }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    @php
                                        $statusColor = match($txn->status) {
                                            'Reviewed' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
                                            'Pending' => 'bg-amber-100 text-amber-700 ring-amber-600/20',
                                            default => 'bg-gray-100 text-gray-700 ring-gray-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusColor }}">
                                        {{ $txn->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<script>
document.getElementById('per_page').addEventListener('change', function () {
    this.closest('form').submit();
});
</script>
@endsection
