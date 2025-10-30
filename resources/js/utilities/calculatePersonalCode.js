export function calculatePersonalCode({
  name,
  surname,
  sex,
  birthDate, // formato YYYY-MM-DD
  birthCity,
  citiesList // array di comuni con name e code (o codiceCatastale ecc.)
}) {
  if (!name || !surname || !sex || !birthDate || !birthCity || !citiesList) {
    return "";
  }

  const toUpper = (str = "") =>
    String(str)
      .toUpperCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();

  const onlyLetters = (s = "") => toUpper(s).replace(/[^A-Z]/g, "");
  const consonants = (s = "") => onlyLetters(s).replace(/[AEIOU]/g, "");
  const vowels = (s = "") => onlyLetters(s).replace(/[^AEIOU]/g, "");

  // --- surname 3 chars
  const encodeSurname = (s) => {
    const cons = consonants(s);
    const voc = vowels(s);
    return (cons + voc + "XXX").slice(0, 3);
  };

  // --- name 3 chars with special rule (if cons >=4 take 1,3,4)
  const encodeName = (n) => {
    const cons = consonants(n);
    const voc = vowels(n);
    if (cons.length >= 4) {
      return (cons[0] || "") + (cons[2] || "") + (cons[3] || "");
    }
    return (cons + voc + "XXX").slice(0, 3);
  };

  // --- date parts
  const date = new Date(birthDate);
  if (Number.isNaN(date.getTime())) return "";

  const year = String(date.getFullYear()).slice(-2);
  const monthCodes = ["A", "B", "C", "D", "E", "H", "L", "M", "P", "R", "S", "T"];
  const month = monthCodes[date.getMonth()] || "X";
  let day = date.getDate();
  if (String(sex).toLowerCase() === "d" || String(sex).toLowerCase() === "f") {
    // allow 'd' or 'f' for donna if you use that; adapt if your app uses different letters
    day += 40;
  }
  const dayStr = String(day).padStart(2, "0");

  // --- find comune and its codice catastale (4 chars)
  const normalizedBirthCity = toUpper(birthCity).replace(/\s+/g, " ").trim();

  const city = citiesList.find((c) => {
    // try multiple name keys and normalize
    const names = [];
    if (c.name) names.push(String(c.name));
    if (c.nome) names.push(String(c.nome));
    // sometimes city objects contain alternative names, try them
    if (c.comune) names.push(String(c.comune));
    return names
      .map((n) => toUpper(n).replace(/\s+/g, " ").trim())
      .some((n) => n === normalizedBirthCity);
  });

  // support different keys for the code: code, codice, codiceCatastale, codice_catastale
  let comuneCode = "";
  if (city) {
    comuneCode =
      city.cadastralCode
        .toString()
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "")
        .slice(0, 4);
  } else {
    // none found: try best-effort partial match (startsWith) or fallback
    const partial = citiesList.find((c) =>
      toUpper(c.name || c.nome || "").includes(normalizedBirthCity)
    );
    if (partial) {
      comuneCode =
        (partial.code || partial.codice || partial.codiceCatastale || "")
          .toString()
          .toUpperCase()
          .replace(/[^A-Z0-9]/g, "")
          .slice(0, 4);
    } else {
      comuneCode = ""; // will be validated below
    }
  }

  // If comuneCode not valid (not 4 chars), fallback to 'Z000' to keep length predictable
  if (!comuneCode || comuneCode.length !== 4) {
    // log per debug (rimuovi in produzione)
    // console.warn(`[CF] comune non trovato o codice non valido per "${birthCity}" — uso fallback Z000`);
    comuneCode = "Z000";
  }

  const part =
    encodeSurname(surname) +
    encodeName(name) +
    year +
    month +
    dayStr +
    comuneCode;

  // Sanity check: part must have length 15
  if (part.length !== 15) {
    // log per debug
    // console.error("[CF] lunghezza parziale non corretta:", part, part.length);
    // Tentativo di correzione forzata: tronca o padding a destra con X fino a 15
    const fixed = (part + "XXXXXXXXXXXXXXX").slice(0, 15);
    // continue with fixed
    return fixed + calculateControlChar(fixed);
  }

  // calcolo carattere di controllo
  const control = calculateControlChar(part);
  return part + control;
}

// funzione carattere di controllo — mapping standard
function calculateControlChar(cf15) {
  const oddMap = {
    "0": 1,
    "1": 0,
    "2": 5,
    "3": 7,
    "4": 9,
    "5": 13,
    "6": 15,
    "7": 17,
    "8": 19,
    "9": 21,
    A: 1,
    B: 0,
    C: 5,
    D: 7,
    E: 9,
    F: 13,
    G: 15,
    H: 17,
    I: 19,
    J: 21,
    K: 2,
    L: 4,
    M: 18,
    N: 20,
    O: 11,
    P: 3,
    Q: 6,
    R: 8,
    S: 12,
    T: 14,
    U: 16,
    V: 10,
    W: 22,
    X: 25,
    Y: 24,
    Z: 23,
  };
  const evenMap = {
    "0": 0,
    "1": 1,
    "2": 2,
    "3": 3,
    "4": 4,
    "5": 5,
    "6": 6,
    "7": 7,
    "8": 8,
    "9": 9,
    A: 0,
    B: 1,
    C: 2,
    D: 3,
    E: 4,
    F: 5,
    G: 6,
    H: 7,
    I: 8,
    J: 9,
    K: 10,
    L: 11,
    M: 12,
    N: 13,
    O: 14,
    P: 15,
    Q: 16,
    R: 17,
    S: 18,
    T: 19,
    U: 20,
    V: 21,
    W: 22,
    X: 23,
    Y: 24,
    Z: 25,
  };
  const controlChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  let sum = 0;
  for (let i = 0; i < cf15.length; i++) {
    const ch = cf15[i];
    sum += i % 2 === 0 ? oddMap[ch] ?? 0 : evenMap[ch] ?? 0;
  }
  return controlChars[sum % 26];
}