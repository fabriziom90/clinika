<script setup>
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  title: {
    type: String,
    default: "Export"
  },
  columns: {
    type: Array,
    required: true
  },
  rows: {
    type: Array,
    required: true
  },
  filename: {
    type: String,
    default: "export.pdf"
  },
  auditType: {
    type: String,
    required: true
  },
  auditId: {
    type: [Number, String],
    default: null
  }
});

const exportPdf = async () => {
  const doc = new jsPDF();

  doc.setFontSize(16);
  doc.text(props.title, 14, 20);

  autoTable(doc, {
    startY: 30,
    head: [props.columns],
    body: props.rows
  });

  doc.save(props.filename);

  try {
    router.post(route("admin.audits.export"), {
      auditable_type: props.auditType,
      auditable_id: props.auditId,
      tags: props.auditTags
    });
  } catch (error) {
    console.error("Errore registrazione audit esportazione:", error);
  }
};
</script>

<template>
  <button class="main-button" @click="exportPdf">
    Esporta PDF
  </button>
</template>

<style lang="scss" scoped>
@use '../../scss/app.scss' as *;

.main-button {
  padding: 10px 20px !important;
}
</style>