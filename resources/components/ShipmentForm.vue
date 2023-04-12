<template>
  <div class="bg-white card shadow py-3 px-4">
    <div class="row border-bottom mb-2">
      <span class="h5">Shipment Information</span>
    </div>

    <div class="row">
      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label" >Purchase Order</label>
        <multi-select v-model="purchaseOrder"
                      :options="purchaseOrders"
                      label="id"
                      track-by="id"
                      @select="setSupplier"
                      @search-change="fetchPurchaseOrders">
        </multi-select>
        <input type="hidden" name="purchase_order_id" v-if="purchaseOrder" :value="purchaseOrder.id">
        <input type="hidden" name="supplier_id" v-if="purchaseOrder" :value="purchaseOrder.supplier_id">
        <input type="hidden" name="ship_from" v-if="purchaseOrder" :value="purchaseOrder.ship_from">
        <input type="hidden" name="ship_to" v-if="purchaseOrder" :value="purchaseOrder.ship_to">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Supplier</label>
        <input class="form-control" name="supplier_name" type="text" v-model="supplier" readonly>
      </div>

      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Shipment Value</label>
        <input class="form-control" type="number" :name="'value'" placeholder="$(SGD)" v-model="shipment.value">
      </div>

      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Courier</label>
        <multi-select v-model="courier"
                      :options="couriers"
                      :label="'name'"
                      :track-by="'id'"
                      @search-change="fetchCouriers">
        </multi-select>
        <input type="hidden" name="courier_id" v-if="courier" :value="courier.id">
        <input type="hidden" name="shipment_provider" v-if="courier" :value="courier.name">
      </div>

      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Shipment Date</label>
        <input  class="form-control" type="date" :name="'shipment_date'" v-model="shipment.shipment_date">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">ETA Date</label>
        <input  class="form-control" type="date" :name="'eta_date'" v-model="shipment.eta_date">
      </div>

      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Weight</label>
        <input  class="form-control" type="number" step="0.001" :name="'weight'" placeholder="Weight (KG)" v-model="shipment.weight">
      </div>
      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Shipping Fee</label>
        <input class="form-control" type="number" step="0.01" :name="'shipping_fee'" placeholder="Shipping Fee"  v-model="shipment.shipping_fee">
      </div>

      <div class="col-12 col-md-6 mb-2">
        <label class="form-check-label">Tracking Number</label>
        <input  class="form-control" type="number" :name="'tracking_number'" placeholder="Tracking Number"  v-model="shipment.tracking_number">
      </div>
      <!--            <div class="col-12 col-md-6 mb-2">-->
      <!--                <label class="form-check-label">Status</label>-->
      <!--                <select class="form-select" :name="'status'">-->
      <!--                    <option v-for="(option, key) in status" :value="key">{{ option }}</option>-->
      <!--                </select>-->
      <!--            </div>-->
    </div>
  </div>
</template>

<script>

export default {
  name: "ShipmentForm",
  props: ['edit_shipment'],
  data() {
    return {
      isEdit: false,
      purchaseOrder: null,
      courier: null,
      supplier: null,
      suppliers: [],
      couriers: [],
      purchaseOrders: [],
      shipment: {
        tracking_number : null,
        shipping_fee : 0,
        value : 0,
        weight : 0,
        shipment_date : null,
        eta_date : null,
        status : null,
      }
    }
  },

  created() {
    this.fetchPurchaseOrders();
    this.fetchCouriers();
    if (typeof(this.edit_shipment) != 'undefined' ) {
      this.isEdit = true;
      this.shipment = this.edit_shipment;
    }
  },

  methods: {
    fetchPurchaseOrders(query) {
      let params = [{
        'search': query,
        'status': 1,
        'with': 'supplier'
      }];
      axios.get(`/web/purchase-orders`, {
        params: { params }
      }).then((response) => {
        this.purchaseOrders = response.data.response.items;
      });
    },
    fetchCouriers(query) {
      let params = [{
        'search': query,
        'status': 1,
        'with': 'supplier'
      }];
      axios.get(`/web/couriers`, {
        params: { params }
      }).then((response) => {
        this.couriers = response.data.response.items;
      });
    },
    setSupplier() {
      this.supplier = this.purchaseOrder.supplier.name
    },
  }
}
</script>

