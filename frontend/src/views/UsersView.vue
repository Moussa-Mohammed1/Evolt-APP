<script setup>
import { onMounted, ref } from 'vue';
import { getUsers } from '../services/userService';

const users = ref([]);
const error = ref('');

onMounted(async () => {
  try {
    const res = await getUsers();
    users.value = res?.data?.users ?? res?.data ?? [];
  } catch (err) {
    error.value =
      err?.response?.data?.message ||
      'Error happened while trying to get users! Try again later.';
  }
});
</script>
<template>
  <div>
    <p v-if="error">{{ error }}</p>
    <table v-if="users.length">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in users" :key="u.id">
          <td>{{ u.name }}</td>
          <td>{{ u.email }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else>No users found.</p>
  </div>
</template>