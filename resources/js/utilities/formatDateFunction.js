export const formatDate = (value) => {
    if (!value) return "";
    const date = new Date(value);
    if (isNaN(date)) return value; // non è una data valida
    return date.toLocaleDateString("it-IT"); // formato dd/mm/yyyy
};