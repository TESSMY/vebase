<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $invoice->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css" media="screen">
        html {
            font-family: sans-serif;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            font-size: 10px;
            margin: 36pt;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 0px;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
        }

        th {
            text-align: inherit;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 10px;
        }

        td {
            padding-left: 10px;
        }

        tr {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        h4,
        .h4 {
            font-weight: 400;
            line-height: 1.2;
        }

        h4,
        .h4 {
            font-size: 2.5rem;
        }

        .table {
            width: 100%;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border: 1px solid #dee2e6;
        }

        .table tbody+tbody {
            border: 1px #dee2e6;
        }

        .mt-1 {
            margin-top: 1rem !important;
        }

        .mt-5 {
            margin-top: 3rem !important;
        }

        .mb-1 {
            margin-bottom: 0.5rem !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .pl-10 {
            padding-left: 10px;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-center textarea {
            display: inline-block;
            text-align: left;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        .w-200 {
            width: 200px;
        }

        .float-right {
            float: right;
        }

        .float-left {
            float: left;
        }

        .w-45-p {
            width: 45%;
        }


        * {
            font-family: "DejaVu Sans";
        }

        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        .party-header {
            font-weight: bold;
        }

        .total-amount {
            font-size: 12px;
            font-weight: 700;
        }

        .border-0 {
            border: none !important;
        }

        .blue-hr {
            border-color: #737DF2;
            background-color: #737DF2;
            padding-top: 10px;
            border-style: solid;
            border-width: 2px;
        }

        .text-big {
            font-size: 16px;
        }

        .grey {
            background-color: #F1F3FA;
            padding-left: 10px;
            padding-right: 10px;
        }

        .px-10 {
            padding-left: 10px;
            padding-right: 10px;
        }

        .py-10 {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .py-5 {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .thick-th {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .middle-hr {
            padding-top: 50px;
            text-align: center;
        }

        .pt-100 {
            padding-top: 100px;
        }

        .pt-200 {
            padding-top: 200px;
        }

        .grand-total-text {
            color: #737DF2;
        }
    </style>
</head>

<body>

<div>
    <div class="float-left">
        @if($invoice->logo)
        {{-- <img src="{{ $invoice->getLogo() }}" alt="logo" height="100"> --}}
        @endif
    </div>

    <div class="float-right">
        <div class="row">
            <b>Date:&emsp;</b>{{ now()->format('d-m-Y') }}
        </div>
        <div class="row">
            <b>R.F.Q No:&emsp;</b>{{ $invoice->getCustomData()[0]->id }}
        </div>
    </div>
</div>

<div class="middle-hr">
    <b class="text-big">Request for Quotation</b>
    <hr class="blue-hr">
</div>

<div>
    <div class="float-left w-45-p">
        <table>
            <tr>
                <th class="grey">Vendor</th>
            </tr>
            <tr>
                <th>hoho</th>
                {{-- <th>{{ $invoice->getCustomData()[0]->supplier->address }}</th> --}}
            </tr>
        </table>
    </div>

    <div class="float-right w-45-p">
        <table>
            <tr>
                <th class="grey">Ship To</th>
            </tr>
            <tr>
                <th>{{ $invoice->getCustomData()[0]->ship_to }}</th>
            </tr>
        </table>
    </div>
</div>

<div class="pt-100">
    <table>
        <thead>
            <tr class="grey bold">
                <th>Payment Terms</th>
                <th>Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->getCustomData()[0]->payment_term }}</td>
                <td>{{ $invoice->getCustomData()[0]->delivery_date->format('d/m/Y') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="pt-100">
    <table>
        <thead>
            <tr class="grey bold">
                <th>SKU</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->getCustomData()[0]->purchaseItems as $item)
                <tr>
                    <td>SKU #{{ $item->sku }}</td>
                    <td>{{ !empty($item->product) ? $item->product->name : '-' }}</td>
                    <td>{{ !empty($item->product->brand) ? $item->product->brand->name : '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script type="text/php">
    if (isset($pdf) && $PAGE_COUNT > 1) {
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width);
        $y = $pdf->get_height() - 35;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
</body>

</html>
