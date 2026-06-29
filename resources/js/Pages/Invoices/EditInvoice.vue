<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { useToast } from "vue-toast-notification";

const props = defineProps({
    appointment: Object,
    invoice: Object,
    services: Array,
});

const $toast = useToast();

const stampDutyValue = ref(true);

const form = useForm({
    id: props.invoice.id,
    uuid: props.invoice?.uuid ?? null,

    appointment_id:
        props.invoice?.appointment_id ?? props.appointment?.id ?? null,

    doctor_id: props.invoice?.doctor_id ?? props.appointment?.doctor_id ?? null,

    patient_id:
        props.invoice?.patient_id ?? props.appointment?.patient_id ?? null,

    date: props.invoice?.date ?? new Date().toISOString().substring(0, 10),

    full_name: props.invoice?.full_name ?? "",
    vat_number: props.invoice?.vat_number ?? "",
    address: props.invoice?.address ?? "",
    city: props.invoice?.city ?? "",
    zip_code: props.invoice?.zip_code ?? "",

    description: props.invoice?.description ?? "",

    subtotal: props.invoice?.subtotal ?? 0,
    vat_amount: props.invoice?.vat_amount ?? 0,
    stamp_duty: props.invoice?.stamp_duty ?? 0,
    discount_amount: props.invoice?.discount_amount ?? 0,
    total: props.invoice?.total ?? 0,
    amount: props.invoice?.amount ?? 0,
    status: props.invoice?.status ?? "draft",
    payment_method: props.invoice?.payment_method ?? 'card',
    items: props.invoice?.items?.length
        ? props.invoice.items
        : [
            {
                service_id: props.appointment.service_id,
                description: props.appointment.service.name,
                quantity: 1,
                unit_price: props.appointment.service.default_price,
                vat_percentage: 0,
                total: props.appointment.service.default_price,
            },
        ],
});

watch(
    () => form.items[0].service_id,
    (serviceId) => {

        const service = props.services.find(
            s => s.id === serviceId
        );

        if (!service) return;

        form.items[0].description = service.name;
        form.items[0].unit_price = service.price;
        form.items[0].total = service.price;

        form.subtotal = service.price;


        form.total = service.price;
        form.amount = service.price;

    }
)

// totale singola riga
const calculateItemTotal = (item) => {
    const imponibile = Number(item.quantity) * Number(item.unit_price);

    const iva = imponibile * (Number(item.vat_percentage) / 100);

    return (imponibile + iva).toFixed(2);
};

// ricalcolo automatico
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + Number(item.quantity) * Number(item.unit_price);
    }, 0);
});

const vatAmount = computed(() => {
    return form.items.reduce((sum, item) => {
        const imponibile = Number(item.quantity) * Number(item.unit_price);

        return sum + imponibile * (Number(item.vat_percentage) / 100);
    }, 0);
});

const total = computed(() => {
    const discount = subtotal.value * form.discount_amount / 100;
    return (
        subtotal.value +
        vatAmount.value +
        Number(form.stamp_duty) -
        discount
    ).toFixed(2);
});

const amount = computed(() => {
    return total.value;
});

watch(
    subtotal,
    (value) => {
        if (!stampDutyValue) return;
        form.stamp_duty = value >= 77.46 ? 2 : 0
    },
    { immediate: true }
)


// righe fattura

const addItem = () => {

    form.items.push({
        service_id: null,
        description: "",
        quantity: 1,
        unit_price: 0,
        vat_percentage: 0,
        total: 0,

    });

};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.subtotal = subtotal.value;
    form.vat_amount = vatAmount.value;
    form.total = total.value;
    form.amount = amount.value;


    form.put(route("admin.invoices.update", form.uuid),
        {
            onError: (err) => {
                const flat = Object.values(err).flat();
                $toast.error(flat.join("\n"));

            },
        });
};
</script>

<template>

    <Head title="Crea fattura" />
    <AuthenticatedLayout section="invoices">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h2>Crea fattura</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>Intestatario</label>
                    <input class="form-control" v-model="form.full_name" />
                </div>
                <div class="col-md-6">
                    <label>Partita IVA / Codice Fiscale</label>
                    <input class="form-control" v-model="form.vat_number" />
                </div>
                <div class="col-md-6 mt-3">
                    <label>Indirizzo</label>
                    <input class="form-control" v-model="form.address" />
                </div>
                <div class="col-md-3 mt-3">
                    <label>Città</label>
                    <input class="form-control" v-model="form.city" />
                </div>

                <div class="col-md-3 mt-3">
                    <label>CAP</label>
                    <input class="form-control" v-model="form.zip_code" />
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Prestazione</th>
                                <th>Descrizione</th>
                                <th>Q.tà</th>
                                <th>Prezzo</th>
                                <th>IVA %</th>
                                <th>Totale</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td>
                                    <select v-model="form.items[index].service_id" class="form-select">
                                        <option value="">Seleziona prestazione</option>

                                        <option v-for="service in services" :key="service.id" :value="service.id">
                                            {{ service.name }} - {{ service.price }} €
                                        </option>

                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" v-model="item.description" />
                                </td>
                                <td width="100">
                                    <input type="number" min="1" class="form-control" v-model="item.quantity" />
                                </td>
                                <td width="130">
                                    <input type="number" step="0.01" class="form-control" v-model="item.unit_price" />
                                </td>
                                <td width="100">
                                    <input type="number" step="0.01" class="form-control"
                                        v-model="item.vat_percentage" />
                                </td>
                                <td>{{ calculateItemTotal(item) }} €</td>
                                <td>
                                    <button type="button" class="btn btn-danger" @click="removeItem(index)">
                                        ×
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="secondary-button" @click="addItem">
                        Aggiungi voce
                    </button>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <label>Descrizione fattura</label>
                    <textarea rows="4" class="form-control" v-model="form.description"></textarea>
                </div>
                <div class="col-md-6">
                    <h5>Totali</h5>
                    <div class="card">
                        <div class="card-body">
                            <p>
                                Subtotale:
                                {{ subtotal.toFixed(2) }} €
                            </p>
                            <p>
                                IVA:
                                {{ vatAmount.toFixed(2) }} €
                            </p>
                            <p>
                                Bollo:
                                <input type="number" step="0.01" class="form-control" v-model="form.stamp_duty" />
                            </p>
                            <p>
                                Sconto:
                                <input type="number" step="0.01" class="form-control" v-model="form.discount_amount" />
                            </p>
                            <p>
                                Metodo pagamento:
                                <select class="form-select" v-model="form.payment_method">
                                    <option value="">Seleziona metodo di pagamento</option>
                                    <option value="cash">Contanti</option>
                                    <option value="card">Carta</option>
                                    <option value="bank_transfer">Bonifico</option>
                                </select>
                            </p>
                            <hr />
                            <h4>
                                Totale:
                                {{ total }} €
                            </h4>
                            <h5>
                                Da pagare:
                                {{ amount }} €
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
                <Link :href="route('admin.invoices.index')" class="secondary-button">
                    Annulla
                </Link>

                <button class="main-button" @click="submit">
                    Salva
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
