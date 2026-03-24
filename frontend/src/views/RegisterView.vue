<script setup>
import { ref } from 'vue';
import api from '../api/axios';
import router from '../router';

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const message = ref([]);
const loading = ref(false);

async function submitRegister() {
	message.value = [];
	loading.value = true;
	try {
		const res = await api.post('/register', {
			name: name.value,
			email: email.value,
			password: password.value,
			confirmPassword: confirmPassword.value
		});
		message.value = [res.data?.message || 'registered'];
		await router.push('/stations');

	} catch (error) {
		const validationErrors = error.response?.data?.errors;
		if (validationErrors && typeof validationErrors === 'object') {
			message.value = Object.values(validationErrors)
				.flat()
				.filter((item) => typeof item === 'string' && item.length > 0);
		} else if (Array.isArray(error.response?.data?.message)) {
			message.value = error.response.data.message;
		} else if (typeof error.response?.data?.message === 'string') {
			message.value = [error.response.data.message];
		} else {
			message.value = ['Try again'];
		}
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<main class="page">
		<section class="card">
			<h1>Inscription</h1>
			<div>
				<ul v-if="message.length" class="message-list">
					<li v-for="(m, index) in message" :key="index">{{ m }}</li>
				</ul>
			</div>

			<form class="form" @submit.prevent="submitRegister">
				<label>
					Nom complet
					<input v-model="name" type="text" placeholder="Votre nom" />
				</label>

				<label>
					Email
					<input v-model="email" type="email" placeholder="vous@email.com" />
				</label>

				<label>
					Mot de passe
					<input v-model="password" type="password" placeholder="********" />
				</label>

				<label>
					Confirmer le mot de passe
					<input v-model="confirmPassword" type="password" placeholder="********" />
				</label>

				<button type="submit">{{ loading ? 'Creating account ...' : 'Creer le compte' }}</button>
			</form>
			<p class="links">
				<RouterLink to="/">Accueil</RouterLink>
				<RouterLink to="/login">Connexion</RouterLink>
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
	max-width: 500px;
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
	background: #1f9d68;
	color: #ffffff;
	cursor: pointer;
}

.message {
	margin-top: 12px;
	color: #116946;
}

.message-list {
	margin: 12px 0 0;
	padding-left: 18px;
	color: #b42318;
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
