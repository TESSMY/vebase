<div class="row">
    <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Client</label>
        <input class="form-control" list="datalistOptions" placeholder="Type to search...">
            <datalist id="datalistOptions">
            <option value="San Francisco">
            <option value="New York">
            <option value="Seattle">
            <option value="Los Angeles">
            <option value="Chicago">
        </datalist>
    </div>
    <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Date</label>
        <input class="form-control" type="date" name="name" placeholder="date" value="{{ old('date') ?? (!empty($invoice) ? $invoice->date : '') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label class="form-label">Client Address</label>
        <input class="form-control" type="text" placeholder="Client Address" disabled>
    </div>
    <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Payment Term</label>
        <input class="form-control" type="text" name="payment_term" placeholder="Payment Term" value="{{ old('payment_term') ?? (!empty($invoice) ? $invoice->payment_term : '') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Sales Order (Optional)</label>
        <input class="form-control" list="datalistOptions" placeholder="Type to search...">
            <datalist id="datalistOptions">
            <option value="San Francisco">
            <option value="New York">
            <option value="Seattle">
            <option value="Los Angeles">
            <option value="Chicago">
        </datalist>
    </div>
</div>