<x-app-layout>
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="row justify-content-between align-items-center w-100 border-bottom pb-4">
            <div class="col-md-12">
                <span>
                    <h5><i class="fa fa-store text-identity me-4" style="color: #289983"></i>Product List</h5>
                </span>
            </div>
        </div>
        {{-- search and button --}}
        <div class="row my-4">
            <div class="d-flex justify-content-between align-items-end">
                <div class="col-12 col-md-auto d-flex">
                    <a href="#" class="btn btn-primary px-5">Add Products</a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <form id="frmSearch" action="#" method="GET">
                        @csrf
                        <div class="filter">
                            <div class="d-flex justify-content-between align-items-center filter-search px-2 py-2">
                                <input type="text" name="q" placeholder="Search in the current filter" value="" class="w-100">
                                <i class="bi bi-search" onclick="document.getElementById('frmSearch').submit()" style="cursor: pointer;"></i>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- index table --}}
        <div class="row mt-2">
            <div class="table-responsive">
                <table class="table table-rounded table-striped shadow w-100 mt-4">
                    <thead>
                        <tr>
                            <th class="px-5" style="width:15%">Product</th>
                            <th style="width:15%">Sku</th>
                            <th style="width:15%">Supplier</th>
                            <th style="width:35%">Brands</th>
                            <th style="width:35%">Piece per Carton</th>
                            <th style="width:35%">Total Stock</th>
                            <th style="width:35%">Available Stock</th>
                            <th style="width:35%">Sales Order Stock</th>
                            <th style="width:35%">Retail Selling Price</th>
                            <th style="width:15%">Unit Cost</th>
                            <th style="width:15%">Lead Time</th>
                            <th style="width:15%">Safety Stock</th>
                            <th style="width:15%">Updated At</th>
                            <th style="width:15%">Status</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-inline-flex">
                                    <img src="" width="50" height="50" />
                                    <span class="ps-2">Adirondack Chair</span>
                                </div>
                            </td>
                            <td>SKU #12345</td>
                            <td>Supplier #12345</td>
                            <td>Brand Name</td>
                            <td>15</td>
                            <td>150</td>
                            <td>150</td>
                            <td>150</td>
                            <td>$45.20</td>
                            <td>$36.20</td>
                            <td>3 Days</td>
                            <td>50</td>
                            <td>10/02/2023 15:35 PM</td>
                            <td><button class="btn btn-success btn-sm w-100">Active</button></td>
                            <td><a href="#" class="btn btn-primary px-5">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
