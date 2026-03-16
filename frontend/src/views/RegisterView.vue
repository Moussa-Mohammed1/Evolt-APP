<script setup>
import { ref } from 'vue';

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const message = ref('');

function submitRegister() {
	if (!name.value || !email.value || !password.value || !confirmPassword.value) {
		message.value = 'Veuillez remplir tous les champs.';
		return;
	}

	if (password.value !== confirmPassword.value) {
		message.value = 'Les mots de passe ne correspondent pas.';
		return;
	}

	message.value = 'Inscription envoyee (demo).';
}
</script>

<template>
	<main class="page">
		<section class="card">
			<h1>Inscription</h1>

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
					<input
						v-model="confirmPassword"
						type="password"
						placeholder="********"
					/>
				</label>

				<button type="submit">Creer le compte</button>
			</form>

			<p v-if="message" class="message">{{ message }}</p>

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
