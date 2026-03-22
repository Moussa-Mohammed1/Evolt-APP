<script setup>
import { ref } from "vue";
import api from "../services/api";
import router from "../router";

const email = ref("");
const password = ref("");
const message = ref("");
const loading = ref(false);

async function submitLogin() {
  loading.value = true;
  if (!email.value || !password.value) {
    message.value = "Veuillez remplir email et mot de passe.";
    return;
  }
  try {
    const res = await api.post("/login", {
      email: email.value,
      password: password.value,
    });
    message.value = res.data?.message || "nothing new";
    await router.replace("/stations");
  } catch (error) {
    message.value = error.response?.data?.message || "Connexion failed! Try again later";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-black w-full">
    <div class="bg-white shadow-lg p-8 w-96">
      <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
      <form class="space-y-4" @submit.prevent="submitLogin">
        <input
          v-model="email"
          type="email"
          placeholder="Email"
          class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <input
          v-model="password"
          type="password"
          placeholder="Password"
          class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
          {{ loading ? "loading..." : "login" }}
        </button>
        <p v-if="message" class="success">
          {{ message }}
        </p>
      </form>
    </div>
  </div>
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
