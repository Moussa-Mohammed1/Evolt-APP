<script setup>
import { ref } from "vue";
import api from "../api/axios";
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
    const token = res.data?.token;
    const user = res.data?.user;

    localStorage.setItem('token', token || "");
    localStorage.setItem('user', JSON.stringify(user ?? null))
    await router.replace("/stations");
  } catch (error) {
    message.value = error.response?.data?.message || "Connexion failed! Try again later";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center w-full bg-slate-300">
    <div class="bg-white shadow-lg p-8 w-96 rounded-sm">
      <h2 class="text-2xl font-bold mb-6 text-left text-black">Login</h2>
      <div class="h-0.5 my-1 bg-black w-full"></div>
      <form class="max-w-sm mx-auto space-y-4" @submit.prevent="submitLogin">
        <div>
          <label for="visitors" class="text-left mb-2.5 text-sm font-medium text-black"
            >Email</label
          >
          <input
            type="text"
            v-model="email"
            id="visitors"
            class="bg-neutral-secondary-medium text-black border border-default-medium text-heading text-sm rounded-base focus:ring-2 focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
            placeholder=""
            required
          />
        </div>
        <div>
          <label for="visitors" class="my-6 text-left text-sm font-medium text-black"
            >Password</label
          >
          <input
            v-model="password"
            type="text"
            id="visitors"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            placeholder=""
            required
          />
        </div>
        <button
          class="bg-slate-400 rounded-sm text-black py-1.5 px-3 font-semibold"
          type="submit"
          >{{ loading ? 'loading' : 'login' }}</button>
        <p>{{ message }}</p>
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
