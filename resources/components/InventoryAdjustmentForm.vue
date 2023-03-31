<template>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row border-bottom mb-2">
            <span class="h5">Adjustment Information</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Adjustment Type</label>
                <input class="form-control" type="text" value="Quantity Adjustment" placeholder="Adjustment Type" disabled>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Reference</label>
                <input class="form-control" type="text" name="reference" placeholder="Reference" v-model="date">
            </div>
            <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="5" style="resize: none;"></textarea>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Reason</label>
                <input class="form-control" type="text" name="reason" placeholder="reason" required>
            </div>
        </div>
    </div>
    <div class="border my-2 mb-3"></div>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row mb-2">
            <span class="h4">Product Details</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Available Stock</th>
                    <th>New Stock Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in products">
                    <td>
                        <template v-if="item.product.product_id === undefined"> <!-- bundle type  -->
                            <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.product.id">
                        </template>
                        <template v-else> <!-- single and variant type  -->
                            <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.product.product_id">
                            <input type="hidden" :name="'products[' + index + '][product_variant_id]'" :value="item.product.id">
                        </template>
                        <multi-select placeholder="Search Products"
                            v-model="item.product"
                            label="name"
                            :options="productArray.options"
                            @search-change="fetchProducts">
                        </multi-select>
                    </td>
                    <td>
                        <input class="form-control" type="number" v-model="item.product.quantity" disabled>
                    </td>
                    <td>
                        <input class="form-control" type="number" min="0" :name="'products[' + index + '][new_value]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
                    </td>
                    <td>
                        <span class="btn" @click="removeProduct(index)">
                            <i class="uil-trash" style="color: red"></i>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <span class="col-12 col-md-1 mb-5 btn btn-primary" @click="addProduct()">Add More</span>
        <div class="row col-12">
            <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Create</button>
            <a href="/admin/inventory-adjustments" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, reactive, ref } from 'vue';

const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);

function addProduct() {
    products.value.push({
        'product': '',
        'quantity': 0,
        'subTotal': 0,
    })
}

function removeProduct(index) {
    products.value.splice(index, 1);
}

const productArray = reactive({ options: [] });
const fetchProducts = (query) => {
    if (query) {
        axios.get(`/web/products?search=${query}`).then((response) => {
            productArray.options = []
            response.data.response.items.forEach(product => {
                if (product.type == 3) { // bundle type
                    productArray.options.push(product);
                } else {
                    product.variants.forEach(variant => {
                        productArray.options.push(variant)
                    });
                }
            });
        });
    }
};

</script>