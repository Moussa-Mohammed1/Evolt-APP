<script setup>
import { ref } from "vue";

const email = ref("");
const password = ref("");
const message = ref("");

function submitLogin() {
  if (!email.value || !password.value) {
    message.value = "Veuillez remplir email et mot de passe.";
    return;
  }

  message.value = "Connexion envoyee (demo).";
}
</script>

<template>
  <main class="page">
    <section class="card">
      <h1>Connexion</h1>

      <form class="form" @submit.prevent="submitLogin">
        <label>
          Email
          <input v-model="email" type="email" placeholder="vous@email.com" />
        </label>

        <label>
          Mot de passe
          <input v-model="password" type="password" placeholder="********" />
        </label>

        <button type="submit">Se connecter</button>
      </form>

      <p v-if="message" class="message">{{ message }}</p>

      <p class="links">
        <RouterLink to="/">Accueil</RouterLink>
        <RouterLink to="/register">Inscription</RouterLink>
      </p>
    </section>
  </main>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 24px;
}

.card {
  width: 100%;
  max-width: 460px;
  padding: 28px;
  border: 1px solid #d6d6d6;
  border-radius: 14px;
  background: #ffffff;
}

h1 {
  margin: 0 0 16px;
}

.form {
  display: grid;
  gap: 12px;
}

label {
  display: grid;
  gap: 6px;
  font-size: 0.95rem;
}

input {
  padding: 10px;
  border: 1px solid #b9b9b9;
  border-radius: 8px;
}

button {
  margin-top: 4px;
  padding: 10px;
  border: none;
  border-radius: 8px;
  background: #2f6be2;
  color: #ffffff;
  cursor: pointer;
}

.message {
  margin-top: 12px;
  color: #1f4da9;
}

.links {
  margin-top: 16px;
  display: flex;
  gap: 12px;
}

a {
  color: #2f6be2;
  text-decoration: none;
}
</style>
