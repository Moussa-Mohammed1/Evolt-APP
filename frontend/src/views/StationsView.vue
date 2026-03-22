<script setup {}>
import {ref, onMounted} from 'vue'
import { getStations} from '../services/stationService'

const stations = ref([])
const loading =  ref(true)
const error = ref('')

onMounted(async () => {
    try {
        const res = await getStations()
        stations.value = res.data
    } catch (err) {
        error.value = err
    }
    finally {
        loading.value =  false
    }
})
</script>

<template>
  <div>
    <h1>Stations</h1>

    <p v-if="loading">Loading...</p>
    <p v-else-if="error">{{ error }}</p>

    <ul v-else >
      <li v-for="station in stations" :key="station.id">
        {{ station }}
      </li>
    </ul>
  </div>
</template>