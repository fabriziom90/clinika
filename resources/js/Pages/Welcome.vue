<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    canLogin: Boolean,
});
</script>

<template>

    <Head title="Clinika" />

    <div class="homepage">
        <nav class="navbar navbar-expand-lg homepage-navbar">
            <div class="container">
                <Link href="/" class="navbar-brand">
                    <ApplicationLogo :width="'110px'" color="white" :fontSize="'28px'" />
                </Link>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homepageNavbar"
                    aria-controls="homepageNavbar" aria-expanded="false" aria-label="Apri menu">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div id="homepageNavbar" class="collapse navbar-collapse">

                    <ul class="navbar-nav mx-auto gap-lg-3">

                        <li class="nav-item">
                            <a href="#funzionalita" class="nav-link">
                                Funzionalità
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#come-funziona" class="nav-link">
                                Come funziona
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#contatti" class="nav-link">
                                Contatti
                            </a>
                        </li>

                    </ul>

                    <div v-if="canLogin">

                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn-negative">
                            <i class="fa-solid fa-gauge-high me-2"></i>
                            Dashboard
                        </Link>

                        <Link v-else :href="route('login')" class="btn-negative">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>
                            Accedi
                        </Link>

                    </div>

                </div>

            </div>
        </nav>

        <!-- HERO -->
        <section class="hero">
            <div class="container">
                <div class="row align-items-center min-vh-100 py-5">

                    <div class="col-lg-7">

                        <div class="brand mb-4">
                            <span class="brand-name">
                                <div class="brand mb-4">
                                    <ApplicationLogo :width="'150px'" :color="'#c53238'" :fontSize="'40px'" />
                                </div>
                            </span>
                        </div>

                        <h1 class="display-3 fw-bold mb-4">
                            La gestione della tua clinica,
                            <span class="text-mainred">semplice.</span>
                        </h1>

                        <p class="lead text-muted mb-5">
                            Una piattaforma completa per gestire pazienti,
                            appuntamenti, cartelle cliniche, documenti,
                            fatturazione e molto altro, tutto in un unico posto.
                        </p>

                        <div v-if="canLogin" class="d-flex flex-wrap gap-3">

                            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="main-button">
                                <i class="fa-solid fa-gauge-high me-2"></i>
                                Vai alla Dashboard
                            </Link>

                            <Link v-else :href="route('login')" class="main-button">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>
                                Accedi
                            </Link>

                        </div>

                    </div>

                    <div class="col-lg-5 mt-5 mt-lg-0">

                        <div class="hero-card">

                            <div class="hero-icon">
                                <i class="fa-solid fa-hospital"></i>
                            </div>

                            <h3 class="mt-4">
                                Tutto sotto controllo
                            </h3>

                            <p class="text-muted mb-0">
                                Organizza il lavoro quotidiano della tua
                                struttura e accedi rapidamente alle
                                informazioni che ti servono.
                            </p>

                        </div>

                    </div>

                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="features py-5">
            <div class="container py-5">

                <div class="text-center mb-5">

                    <h2 class="fw-bold">
                        Tutto ciò che serve alla tua clinica
                    </h2>

                    <p class="text-muted">
                        Gli strumenti principali per gestire la struttura
                        in modo semplice e organizzato.
                    </p>

                </div>

                <div class="row g-4">

                    <div class="col-md-6 col-lg-3">

                        <div class="feature-card h-100">

                            <div class="feature-icon">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <h4>
                                Agenda
                            </h4>

                            <p class="text-muted mb-0">
                                Gestisci appuntamenti e attività della
                                giornata in modo semplice e immediato.
                            </p>

                        </div>

                    </div>

                    <div class="col-md-6 col-lg-3">

                        <div class="feature-card h-100">

                            <div class="feature-icon">
                                <i class="fa-solid fa-hospital-user"></i>
                            </div>

                            <h4>
                                Pazienti
                            </h4>

                            <p class="text-muted mb-0">
                                Mantieni organizzate le informazioni e la
                                documentazione dei tuoi pazienti.
                            </p>

                        </div>

                    </div>

                    <div class="col-md-6 col-lg-3">

                        <div class="feature-card h-100">

                            <div class="feature-icon">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>

                            <h4>
                                Cartella clinica
                            </h4>

                            <p class="text-muted mb-0">
                                Gestisci visite, parametri, prescrizioni
                                e documenti all'interno della struttura.
                            </p>

                        </div>

                    </div>

                    <div class="col-md-6 col-lg-3">

                        <div class="feature-card h-100">

                            <div class="feature-icon">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>

                            <h4>
                                Fatturazione
                            </h4>

                            <p class="text-muted mb-0">
                                Tieni sotto controllo fatture, pagamenti
                                e situazione economica della clinica.
                            </p>

                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- CTA -->
        <section class="cta py-5">
            <div class="container py-5">

                <div class="cta-card text-center">

                    <h2 class="fw-bold mb-3">
                        Pronto a gestire la tua clinica?
                    </h2>

                    <p class="text-muted mb-4">
                        Accedi alla piattaforma per iniziare a utilizzare
                        Clinika.
                    </p>

                    <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')" class="main-button">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Accedi a Clinika
                    </Link>

                    <Link v-else-if="$page.props.auth.user" :href="route('dashboard')" class="main-button">
                        <i class="fa-solid fa-gauge-high me-2"></i>
                        Vai alla Dashboard
                    </Link>

                </div>

            </div>
        </section>

        <!-- FOOTER -->
        <footer class="footer py-4">
            <div class="container">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                    <span class="text-muted">
                        © {{ new Date().getFullYear() }} Clinika
                    </span>

                    <span class="text-muted">
                        Gestione clinica semplice e organizzata.
                    </span>

                </div>

            </div>
        </footer>

    </div>

</template>

<style lang="scss" scoped>
@use "../../scss/app.scss";
@use "../../scss/_partials/variables" as *;

.homepage {
    min-height: 100vh;
    background: #fff;
}

.homepage-navbar {
    background-color: $mainRed
}

.navbar-brand {
    display: flex;
    align-items: center;

}

.nav-link {
    color: #fff;
    font-weight: 500;
    transition: opacity 0.2s ease;

    &:hover {
        color: #fff;
        opacity: 0.75;
    }
}

.navbar-toggler {
    border: 0;
    color: #fff;
    font-size: 22px;

    &:focus {
        box-shadow: none;
    }
}

.text-mainred {
    color: $mainRed;
}

.hero {
    background: linear-gradient(135deg,
            #ffffff 0%,
            #fafafa 100%);
}

.brand-name {
    color: $mainRed;
    font-size: 32px;
    font-weight: 700;
}

.hero-card {
    padding: 50px 40px;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.hero-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(197, 50, 56, 0.1);
    color: $mainRed;
    font-size: 30px;
}

.features {
    background: #f8f9fa;
}

.feature-card {
    padding: 30px;
    border-radius: 16px;
    background: #fff;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

    &:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    h4 {
        margin-top: 20px;
        margin-bottom: 10px;
    }
}

.feature-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(197, 50, 56, 0.1);
    color: $mainRed;
    font-size: 22px;
}

.cta-card {
    padding: 60px 30px;
    border-radius: 20px;
    background: #f8f9fa;
}

.footer {
    border-top: 1px solid #eee;
}
</style>
