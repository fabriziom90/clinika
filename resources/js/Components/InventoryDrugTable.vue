<script setup>
import { ref, onMounted, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";

import ExportToPdfButton from "./ExportToPdfButton.vue";
import Modal from "./Modal.vue";
import Multiselect from "vue-multiselect";

const props = defineProps({
  items: Array,
  drugs: Array,
  roomId: Number,
});

const emit = defineEmits(["refresh"]);

const $toast = useToast();
const page = usePage();

/* STATE */
const editDrugQuantity = ref(false);
const editDrugExpirationDate = ref(false);
const actualDrug = ref(null);
const editQuantity = ref(null);
const editExpirationDate = ref(null);
const showInventoryDrugForm = ref(false);
const showDeleteModal = ref(false);
const search = ref("");

/* FORM */
const form = useForm({
  drug: "",
  units: null,
  expiry_date: null,
  roomId: props.roomId,
});

/* PDF */
const pdfColumns = ["Nome", "Quantità", "Scadenza"];

const pdfRows = computed(() =>
  filteredDrugs.value.map(ip => [
    ip.drug.name,
    ip.units,
    formatDate(ip.expiry_date)
  ])
);

/* FILTER */
const filteredDrugs = computed(() => {
  if (!props.items) return [];

  if (search.value !== "") {
    return props.items.filter(ip =>
      ip.drug.name.toLowerCase().includes(search.value.toLowerCase())
    );
  }

  return props.items;
});

/* HELPERS */
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const d = String(date.getDate()).padStart(2, "0");
  const m = String(date.getMonth() + 1).padStart(2, "0");
  const y = date.getFullYear();
  return `${d}/${m}/${y}`;
};

const checkExpirationDate = (day) => {
  if (!day) return false;

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const expiration = new Date(day);
  expiration.setHours(0, 0, 0, 0);
  const diffDays = (expiration - today) / (1000 * 60 * 60 * 24);
  
  return diffDays < 15;
};


/* EDIT QUANTITY */
const onEditDrugQuantity = (checked, id, quantity) => {
  editDrugQuantity.value = !checked;

  if (!editDrugQuantity.value) {
    form
      .transform(() => ({ quantity: editQuantity.value }))
      .put(route("admin.inventory-drugs.update-quantity", id));
  } else {
    editQuantity.value = quantity;
    actualDrug.value = id;
  }
};


const onEditDrugExpirationDate = (checked, id, expirationDate) => {
  editDrugExpirationDate.value = !checked;

  if (!editDrugExpirationDate.value) {
    form
      .transform(() => ({ expirationDate: editExpirationDate.value }))
      .put(route("admin.inventory-drugs.update-expiration", id));
  } else {
    const d = new Date(expirationDate);
    editExpirationDate.value =
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    actualDrug.value = id;
  }
};


const handleSubmit = () => {
  form.post(route("admin.inventory-drugs.store"), {
    onSuccess: () => {
      showInventoryDrugForm.value = false;
      form.reset({
        drug: "",
        units: null,
        expirationDate: null,
      });
    },
  });
};

const openModal = (item) => {
  actualDrug.value = item;
  
  showDeleteModal.value = true;
};

const closeModal = () => {
  showDeleteModal.value = false;
  actualDrug.value = null;
};

const confirmDelete = () => {
    closeModal();
};
</script>

<template>
  <div class="col-12 col-md-6" v-if="filteredDrugs">
    <div class="border-right-main p-4">
      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center">
        <h2>Medicinali</h2>
      </div>

      <!-- FORM CREAZIONE -->
      <div v-if="showInventoryDrugForm" class="mt-4">
        <form @submit.prevent="handleSubmit" class="bg-main p-4">
          <div class="row gy-3 mt-2">
            <div class="col-12 col-md-4">
              <Multiselect
                v-model="form.drug"
                :options="drugs.map(p => ({ label: p.name, value: p.id }))"
                placeholder="Cerca..."
                searchable
                clearable
                noOptionsText="Nessun risultato"
              />
            </div>

            <div class="col-12 col-md-4">
              <input
                type="number"
                min="0"
                class="form-control"
                placeholder="Inserisci quantità"
                v-model="form.units"
              />
            </div>

            <div class="col-12 col-md-4">
              <input
                type="date"
                class="form-control"
                v-model="form.expirationDate"
              />
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-white">
                Salva
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- FILTER + EXPORT -->
      <div class="row mt-4">
        <div class="col-8">
          <input
            type="text"
            class="form-control"
            placeholder="Filtra..."
            v-model="search"
          />
        </div>
        <div class="col-4">
          <ExportToPdfButton
            :title="`Inventario medicinali ${page.props.clinicRoom.name}`"
            :columns="pdfColumns"
            :rows="pdfRows"
            :filename="`inventario_medicinali_${page.props.clinicRoom.name}.pdf`"
          />
        </div>
      </div>

      <!-- TABELLA -->
      <table
        v-if="filteredDrugs.length"
        class="table table-striped mt-4"
      >
        <thead>
          <tr>
            <th>Nome</th>
            <th>Quantità</th>
            <th>Scadenza</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="ip in filteredDrugs" :key="ip.id">
            <!-- NOME -->
            <td>
              {{ ip.drug.name }}
              <button class="not-button ms-2" @click="openModal(ip)">
                <i class="fas fa-trash"></i>
              </button>
            </td>

            <!-- QUANTITÀ EDIT INLINE -->
            <td>
              <div class="d-flex align-items-center justify-content-between">
                <span
                  v-if="!editDrugQuantity || actualDrug !== ip.id"
                  :class="ip.units <= 1 ? 'text-danger fw-bold' : ''"
                >
                  {{ ip.units }}
                </span>

                <input
                  v-else
                  type="number"
                  class="form-control me-2"
                  v-model="editQuantity"
                  min="0"
                />

                <button
                  class="btn btn-warning btn-sm"
                  @click="onEditDrugQuantity(editDrugQuantity, ip.id, ip.units)"
                >
                  <i class="fas fa-edit"></i>
                </button>
              </div>
            </td>

            <!-- SCADENZA EDIT INLINE + WARNING -->
            <td>
              <div class="d-flex align-items-center justify-content-between">
                <span
                  v-if="!editDrugExpirationDate || actualDrug !== ip.id"
                  :class="checkExpirationDate(ip.expiry_date)
                    ? 'text-danger fw-bold'
                    : ''"
                >
                  {{ formatDate(ip.expiry_date) }}
                </span>

                <input
                  v-else
                  type="date"
                  class="form-control me-2"
                  v-model="editExpirationDate"
                />

                <button
                  class="btn btn-warning btn-sm"
                  @click="onEditDrugExpirationDate(
                    editDrugExpirationDate,
                    ip.id,
                    ip.expirationDate
                  )"
                >
                  <i class="fas fa-edit"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- EMPTY -->
      <div v-else class="mt-4">
        <h3>Nessun prodotto inserito</h3>
      </div>
    </div>

    <!-- MODAL DELETE -->
    <Modal
      :show="showDeleteModal"
      :item="actualDrug"
      :baseRoute="`admin.inventory-drugs`"
      @close="closeModal"
      @deleted="confirmDelete"
    />
  </div>
</template>


<style lang="scss" scoped>
@use "../../scss/_partials/variables" as *;
@use "../../scss/app.scss" as *;
.border-right-main {
  border-right: 1px solid $mainRed
}

#filter {
  margin: 5px 0px;
}
</style>
