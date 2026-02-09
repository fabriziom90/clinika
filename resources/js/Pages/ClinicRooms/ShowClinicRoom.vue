<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InventoryDrugTable from "@/Components/InventoryDrugTable.vue";
import InventoryProductTable from "@/Components/InventoryProductTable.vue";
import { ref } from "vue";
import { useToast } from "vue-toast-notification";
import ExportToPdfButton from "@/Components/ExportToPdfButton.vue";

const props = defineProps({
    clinicRoom: Object,
    clinicRoomProducts: Array,
    clinicRoomDrugs: Array,
    products: Array,
    drugs: Array,
});

const pdfColumns = ["Nome", "Quantità", "Scadenza"];

const $toast = useToast();

const showAddProductForm = ref(false);
const showAddDrugForm = ref(false);
const localInventoryProducts = ref([...props.clinicRoomProducts]);
const localInventoryDrugs = ref([...props.clinicRoomDrugs]);
const formProduct = useForm({
    room_id: props.clinicRoom.id,
    product_id: "",
    expiry_date: "",
    units: "",
});

const formDrug = useForm({
    room_id: props.clinicRoom.id,
    drug_id: "",
    expiry_date: "",
    units: "",
});

const handleSubmitProductForm = () => {
    formProduct.post(route("admin.inventory-products.store"), {
        onSuccess: (page) => {
            // aggiorna la tabella con i nuovi dati passati dal controller
            localInventoryProducts.value = page.props.inventoryProducts;
            
            formProduct.product_id = "";
            formProduct.expiry_date = "";
            formProduct.units = "";
        },
        onError: (errors) => {
            $toast.error("Errore durante il salvataggio", {
                position: "top-right",
                duration: 3000,
            });
        },
    });
};

const handleSubmitDrugForm = () => {
    formDrug.post(route("admin.inventory-drugs.store"), {
        onSuccess: (page) => {
            // aggiorna la tabella con i nuovi dati passati dal controller
            localInventoryDrugs.value = page.props.inventoryDrugs;

            formDrug.drug_id = "";
            formDrug.expiry_date = "";
            formDrug.units = "";
        },
        onError: (errors) => {
            $toast.error("Errore durante il salvataggio", {
                position: "top-right",
                duration: 3000,
            });
        },
    });
};

const openModal = (currentType) => {
  isModalOpen.value = true;
  type.value = currentType;
};
</script>

<template lang="">
    <Head title="Dettaglio stanza" />
    <AuthenticatedLayout section="clinicrooms">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Dettaglio {{ props.clinicRoom.name }}</h2>
            <div>
                <button
                    class="main-button me-2"
                    @click="showAddProductForm = !showAddProductForm"
                >
                    Aggiungi prodotto
                </button>
                <button
                    class="main-button"
                    @click="showAddDrugForm = !showAddDrugForm"
                >
                    Aggiungi medicinale
                </button>
            </div>
        </div>
        <div v-if="showAddProductForm" class="bg-main-red mb-3 text-white p-3">
            <form
                @submit.prevent="handleSubmitProductForm"
                class="d-flex align-items-end"
            >
                <div class="w-33 me-3">
                    <label class="form-label">Prodotto</label>
                    <select
                        name="product"
                        id="product"
                        class="form-select"
                        v-model="formProduct.product_id"
                    >
                        <option value="">Seleziona prodotto</option>
                        <option
                            :value="product.id"
                            v-for="product in products"
                            :key="product.id"
                        >
                            {{ product.name }}
                        </option>
                    </select>
                </div>
                <div class="w-33 me-3">
                    <label for="" class="form-label">Data scadenza</label>
                    <input
                        type="date"
                        class="form-control"
                        placeholder="Data scadenza"
                        v-model="formProduct.expiry_date"
                    />
                </div>
                <div class="w-33 me-3">
                    <label for="" class="form-label">Quantità</label>
                    <input
                        type="number"
                        min="0"
                        class="form-control"
                        placeholder="Quantità"
                        v-model="formProduct.units"
                    />
                </div>
                <div>
                    <button type="submit" class="btn-negative">Salva</button>
                </div>
            </form>
        </div>
        <div v-if="showAddDrugForm" class="bg-main-red mb-3 text-white p-3">
            <form
                @submit.prevent="handleSubmitDrugForm"
                class="d-flex align-items-end"
            >
                <div class="w-33 me-3">
                    <label class="form-label">Medicinale</label>
                    <select
                        name="drug"
                        id="drug"
                        class="form-select"
                        v-model="formDrug.drug_id"
                    >
                        <option value="">Seleziona medicinale</option>
                        <option
                            :value="drug.id"
                            v-for="drug in drugs"
                            :key="drug.id"
                        >
                            {{ drug.name }}
                        </option>
                    </select>
                </div>
                <div class="w-33 me-3">
                    <label for="" class="form-label">Data scadenza</label>
                    <input
                        type="date"
                        class="form-control"
                        placeholder="Data scadenza"
                        v-model="formDrug.expiry_date"
                    />
                </div>
                <div class="w-33 me-3">
                    <label for="" class="form-label">Quantità</label>
                    <input
                        type="number"
                        min="0"
                        class="form-control"
                        placeholder="Quantità"
                        v-model="formDrug.units"
                    />
                </div>
                <div>
                    <button type="submit" class="btn-negative">Salva</button>
                </div>
            </form>
        </div>
        <div class="row">
            <InventoryProductTable
                :items="clinicRoomProducts"
                
            />
        
            <InventoryDrugTable
                :items="clinicRoomDrugs"
                
            />
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
.w-33 {
    width: calc(100% / 3);
}
</style>
