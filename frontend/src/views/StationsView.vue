<script setup>
import { ref, onMounted } from 'vue'
import { getStations } from '../services/stationService'
import StationCard from '../components/StationCard.vue'

const stations = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const res = await getStations()
    stations.value = res.data
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Failed to load stations'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div v-if="loading">Loading stations...</div>
  <p v-else-if="error">{{ error }}</p>
  
  <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <StationCard v-for="station in stations" :key="station.id" :station="station" />
  </div>
</template>