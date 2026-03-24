<template>
    <nav class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between shadow-lg">

        <!-- Logo -->
        <div class="text-2xl font-bold text-white">
            EVolt
        </div>
        <div class="flex gap-6 items-center">
            <RouterLink to="/" class="hover:text-green-400 transition">Dashboard</RouterLink>
            <RouterLink to="/stations" class="hover:text-green-400 transition">Stations</RouterLink>
            <RouterLink to="/users" class="hover:text-green-400 transition">Users</RouterLink>
        </div>
        <div class="flex gap-4 items-center">

            <template v-if="isAuthenticated">
                <span class="text-sm text-gray-300">
                    {{ user?.name }}
                </span>
                <button @click="logout" class="bg-red-500 px-4 py-2 rounded-lg hover:bg-red-600 transition">
                    Logout
                </button>
            </template>

            <template v-else>
                <RouterLink to="/login" class="bg-green-500 px-4 py-2 rounded-lg hover:bg-green-600 transition">
                    Login
                </RouterLink>
                <RouterLink to="/register"
                    class="border border-green-400 px-4 py-2 rounded-lg hover:bg-green-400 hover:text-black transition">
                    Register
                </RouterLink>
            </template>

        </div>

    </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRouter, RouterLink } from "vue-router";

const router = useRouter();
const token = localStorage.getItem("token");

let user = null;
try {
  const rawUser = localStorage.getItem("user");
  user = rawUser ? JSON.parse(rawUser) : null;
} catch {
  user = null;
  localStorage.removeItem("user");
}

const isAuthenticated = computed(() => !!token);

const logout = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  router.push("/login");
};
</script>