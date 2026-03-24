<script setup>
defineProps({
    station: {
        type: [Object, Array],
        required: true
    }
})
</script>
<template>
    <div class="border rounded-xl p-4 shadow">
        <h2>{{ station.name }}</h2>
        <p>{{ station }}</p>
    </div>
</template>