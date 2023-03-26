<div class="table-responsive mx-3 my-3">
    <table class="table table-rounded table-striped shadow w-100 mt-4">
        <thead>
            <tr class="text-start fw-bold">
                <th class="pb-4 pt-6 px-6">{{ __('Order ID') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Order Type') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Supplier') }}/{{ __('Client') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Quantity') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Amount') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Status') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Date Created') }}</th>
            </tr>
        </thead>
        <tbody>
{{--        @forelse($product->orders as $order)--}}
{{--            <tr>--}}
{{--                <td>{{ __('S.O') }} #{{ $order->id }}</td>--}}
{{--                <td>{{ $order->type }}</td>--}}
{{--                <td>{{ $order->supplier->name }}</td>--}}
{{--                <td>{{ $order->quantity }}</td>--}}
{{--                <td>{{ $order->grand_total }}</td>--}}
{{--                <td>{{ $order->status }}</td>--}}
{{--                <td>{{ $order->created_at }}</td>--}}
{{--            </tr>--}}
{{--        @empty--}}
{{--            <tr>--}}
{{--                <td colspan="7" class="text-center bg-white">There are no orders relating to this product.</td>--}}
{{--            </tr>--}}
{{--        @endforelse--}}
        </tbody>
    </table>
</div>
