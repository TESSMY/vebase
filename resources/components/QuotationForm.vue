<template>
  <div class="row">
    <div class="row border-bottom mb-2">
      <span class="h5">QUOTATION DETAILS</span>
    </div>
    <div class="row">
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Client</label>
        <input type="hidden" :name="'client_id'" :value="selectedClient.id" class="form-control"/>
        <multi-select placeholder="Search Client" v-model="selectedClient" label="name" :options="clientArray" @input="fetchClients" :disabled="disabled"></multi-select>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Delivery Date</label>
        <input type="date" :name="'delivery_date'" class="form-control" v-model="currentQuotation.delivery_date" :disabled="disabled"/>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Quotation Name</label>
        <input type="text" :name="'name'" class="form-control" v-model="currentQuotation.name" :disabled="disabled"/>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Payment Term</label>
        <input type="text" :name="'payment_term'" class="form-control" v-model="currentQuotation.payment_term" :disabled="disabled"/>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Currency</label>
        <input type="text" :name="'currency'" class="form-control" v-model="currentQuotation.currency" :disabled="disabled" required/>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Name</label>
        <input class="form-control" type="text" :name="'billing_name'" v-model="currentQuotation.billing_name" :disabled="disabled" required >
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Contact Number</label>
        <input class="form-control" type="text" :name="'billing_contact_number'" v-model="currentQuotation.billing_contact_number" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Contact Email</label>
        <input class="form-control" type="email" :name="'billing_contact_email'" v-model="currentQuotation.billing_contact_email" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Address 1</label>
        <input class="form-control" type="text" :name="'billing_address_1'" v-model="currentQuotation.billing_address_1" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Address 2</label>
        <input class="form-control" type="text" :name="'billing_address_2'" v-model="currentQuotation.billing_address_2" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing City</label>
        <input class="form-control" type="text" :name="'billing_city'" v-model="currentQuotation.billing_city" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing State</label>
        <input class="form-control" type="text" :name="'billing_state'" v-model="currentQuotation.billing_state" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Postcode</label>
        <input class="form-control" type="text" :name="'billing_postcode'" v-model="currentQuotation.billing_postcode" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Billing Country</label>
        <input class="form-control" type="text" :name="'billing_country'" v-model="currentQuotation.billing_country" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Name</label>
        <input class="form-control" type="text" :name="'ship_to_name'" v-model="currentQuotation.ship_to_name" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Contact Number</label>
        <input class="form-control" type="text" :name="'ship_to_contact_number'" v-model="currentQuotation.ship_to_contact_number" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Contact Email</label>
        <input class="form-control" type="email" :name="'ship_to_contact_email'" v-model="currentQuotation.ship_to_contact_email" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Address 1</label>
        <input class="form-control" type="text" :name="'ship_to_address_1'" v-model="currentQuotation.ship_to_address_1" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Address 2</label>
        <input class="form-control" type="text" :name="'ship_to_address_2'" v-model="currentQuotation.ship_to_address_2" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To City</label>
        <input class="form-control" type="text" :name="'ship_to_city'" v-model="currentQuotation.ship_to_city" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To State</label>
        <input class="form-control" type="text" :name="'ship_to_state'" v-model="currentQuotation.ship_to_state" :disabled="disabled">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Postcode</label>
        <input class="form-control" type="text" :name="'ship_to_postcode'" v-model="currentQuotation.ship_to_postcode" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Ship To Country</label>
        <input class="form-control" type="text" :name="'ship_to_country'" v-model="currentQuotation.ship_to_country" :disabled="disabled" required>
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-label">Expiration Date</label>
        <input type="date" :name="'expiration_date'" class="form-control" v-model="currentQuotation.expiration_date" :disabled="disabled"/>
      </div>
    </div>
  </div>
  <div class="border my-2 mb-3"></div>
  <div class="bg-white card shadow py-3 px-4">
    <div class="row mb-2">
      <span class="h4">ADD PRODUCT</span>
    </div>
    <div class="overflow-auto">
      <table class="table table-bordered text-center">
        <thead>
        <tr>
          <th>Product Details</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Sub Total</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(item, i) in products" :key="i">
          <td>
            <input type="hidden" :name="'products[' + i + '][quotation_item_id]'" :value="item.quotation_item_id">
            <template v-if="item.product.product_id === undefined"> <!-- bundle type  -->
              <input type="hidden" :name="'products[' + i + '][product_id]'" :value="item.product.id">
            </template>
            <template v-else>
              <input type="hidden" :name="'products[' + i + '][product_id]'" :value="item.product.product_id">
              <input type="hidden" :name="'products[' + i + '][product_variant_id]'" :value="item.product.id">
            </template>
            <multi-select placeholder="Search Products"
                          v-model="item.product"
                          label="name"
                          :options="productArray"
                          :disabled="disabled"
                          @search-change="fetchProducts">
            </multi-select>
          </td>
          <td>
            <input class="form-control" type="number" min="0" :name="'products[' + i + '][quantity]'" v-model="item.quantity" @input="updateItemTotalPrice(item)" :disabled="disabled" required>
          </td>
          <td>{{ item.product.selling_price }}</td>
          <td>$ {{ item.subTotal }}</td>
          <input type="hidden" :name="'products[' + i + '][total_price]'" :value="item.subTotal">
          <td><i class="uil-trash cursor-pointer mt-2" @click="removeProduct(i)" :disabled="disabled"></i></td>
        </tr>
        </tbody>
      </table>
      <span class="btn text-primary text-decoration-underline pb-3" @click="addProduct()" :disabled="disabled">Add another item</span>
    </div>
    <div class="row container-fluid">
      <div class="col-12 col-md-4 mb-md-0 mb-3">
        <div class="row px-0">
          <div class="px-0">
            <label class="form-label px-0">Notes</label>
            <textarea class="form-control" placeholder="Notes that will be displayed on sales order." rows="5" style="resize: none" :name="'notes'" v-model="currentQuotation.notes" :disabled="disabled"></textarea>
          </div>
        </div>
      </div>
      <div class="col-md-5"></div>
      <div class="col-12 col-md-3">
        <div class="row text-end">
          <span class="col-7 fw-bold my-auto">Sub Total: </span>
          <span class="col-5">$ {{ currentQuotation.sub_total }}</span>
          <div class="border my-2"></div>
          <span class="col-7 fw-bold my-auto">Tax %: </span>
          <span class="col-5"><input class="form-control" v-model="currentQuotation.tax_rate" @input="updateTotalPrice" type="number" :name="'tax_rate'" min="0" max="100" step="1" :disabled="disabled"></span>
          <div class="border my-2"></div>
          <span class="col-7 fw-bold my-auto">Total (SGD): </span>
          <span class="col-5">$ {{ currentQuotation.grand_total }}</span>
        </div>
      </div>
    </div>

    <input type="hidden" :name="'status'" v-model="currentQuotation.status">
    <div class="row col-md-12 text-end">
      <div class="col-md-12 text-end">
        <button type="submit" @click="currentQuotation.status = 20" class="btn btn-success m-4" :disabled="disabled">Generate S.O.</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "QuotationForm",
  props: ["quotation", "quotationItems", "quotationClient"],

  data() {
    return {
      currentQuotation: {
        expiration_date: '',
        delivery_date: '',
        name: '',
        payment_term: '',
        billing_name: '',
        billing_contact_name: '',
        billing_contact_email: '',
        billing_address_1: '',
        billing_address_2: '',
        billing_city: '',
        billing_state: '',
        billing_postcode: '',
        billing_country: '',
        ship_to_name: '',
        ship_to_contact_number: '',
        ship_to_contact_email: '',
        ship_to_address_1: '',
        ship_to_address_2: '',
        ship_to_city: '',
        ship_to_state: '',
        ship_to_postcode: '',
        ship_to_country: '',
        currency: '',
        item_total: '',
        sub_total: '',
        grand_total: '',
        tax_rate: '',
        notes: '',
        status: 10,
      },
      clientArray: [],
      selectedClient: {},
      productArray: [],
      products: [{
        product: '',
        quantity: 0,
        subTotal: 0
      }],
      disabled: false,
    }
  },

  created() {
    this.fetchClients();
    this.fetchProducts();
  },

  mounted() {
    if (this.quotation !== undefined) {

      this.currentQuotation = this.quotation;
      if (this.currentQuotation.status != 10) {
        this.disabled = true;
      }
      this.selectedClient = this.quotationClient;
      let products = [];

      for (let item of this.quotationItems) {
        let product = {
          quotation_item_id: item.id,
          product: {
            id: item.product_id,
            product_variant_id: item.product_variant_id,
            product_id: item.product_id,
            name: item.name,
            selling_price: item.unit_price,
          },
          quantity: item.quantity,
          subTotal: item.total_price,
        };
        products.push(product);
      }

      this.products = products;
    }
    this.updateItemTotalPrice();
    this.updateTotalPrice();
  },

  methods: {
    fetchClients(query) {
      let parameter = {
        'search': query,
      }
      axios.get(`/web/clients`, {
        params: parameter
      }).then((response) => {
        this.clientArray = response.data.response.items;
      });
    },

    addProduct() {
      this.products.push({
        product: '',
        quantity: 0,
        subTotal: 0,
      })
    },

    removeProduct(i) {
      this.products.splice(i, 1);
      this.updateTotalPrice();
    },

    updateTotalPrice() {
      this.currentQuotation.sub_total = 0;
      this.currentQuotation.grand_total = 0;
      this.currentQuotation.item_total = 0;
      this.products.forEach(item => {
        if (item.product) {
          this.currentQuotation.item_total = item.product.selling_price * item.quantity;
          this.currentQuotation.sub_total += this.currentQuotation.item_total;
        }
      });
      this.currentQuotation.grand_total += parseFloat(this.currentQuotation.sub_total) + (parseFloat(this.currentQuotation.sub_total) * (this.currentQuotation.tax_rate / 100));
    },

    updateItemTotalPrice() {
      this.products.forEach(item => {
        if (item.product) {
          item.subTotal = item.product.selling_price * item.quantity
        }
      });
      this.updateTotalPrice();
    },

    fetchProducts(query) {
      if (query) {
        axios.get(`/web/products?search=${query}`).then((response) => {
          this.productArray = [];
          response.data.response.items.forEach(product => {
            if (product.type === 3) { // bundle type
              this.productArray.push(product);
            } else {
              product.product_variants.forEach(product_variant => {
                this.productArray.push(product_variant);
              });
            }
          });
        })
      }
    }
  }
}
</script>


