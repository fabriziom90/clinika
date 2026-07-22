import { formatDate } from "./formatDateFunction";

export const formatTableValue = (item, key) => {
    // total price
    if (key === "price") {
        const base = item.product || item.drug;
        if (base?.unit_price != null && item.units != null) {
            return `${base.unit_price * item.units}€`;
        }
        return "";
    }

    //consens type
    if(key === 'consent-type.name'){
        return item.consent_type.name;
    }

    //acquisition method
    if(key == "acquisition_method"){
        const labels = {
            "paper": "Cartaceo",
            "upload": "Upload",
            "electronic_signature": "Firma elettronica"
        }

        return labels[item.acquisition_method];

    }

    // for custom item_name
    if (key === "item_name") {
        if (item.product && item.product.name != null) {
            return item.product.name;
        }
        if (item.drug && item.drug.name != null) {
            return item.drug.name;
        }
        return "";
    }

    // others
    const keys = key.split(".");
    let value = item;
    for (let k of keys) {
        if (value == null) return "";
        value = value[k];
    }

    if (
        (key === "is_active" || key === "is_required") &&
        value !== null &&
        value !== undefined
    ) {
        return value ? "Sì" : "No";
    }

    if ((keys[keys.length - 1].toLowerCase().includes("date") || keys[keys.length - 1].toLowerCase().includes("created_at")) && value) {
        return formatDate(value);
    }

    return value ?? "";
}
