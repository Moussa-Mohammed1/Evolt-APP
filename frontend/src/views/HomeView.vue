<template>
	<main class="page">
		<section class="card">
			<h1>Evolt App</h1>
			<p>Bienvenue. Choisissez une action pour commencer.</p>

			<nav class="actions">
				<RouterLink to="/login" class="btn primary">Se connecter</RouterLink>
				<RouterLink to="/register" class="btn">Creer un compte</RouterLink>
			</nav>
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
	padding: 32px;
	border: 1px solid #d6d6d6;
	border-radius: 14px;
	background: #ffffff;
	text-align: center;
}

h1 {
	margin: 0 0 12px;
	font-size: 2rem;
}

p {
	margin: 0;
	color: #565656;
}

.actions {
	margin-top: 24px;
	display: grid;
	gap: 12px;
}

.btn {
	display: inline-block;
	text-decoration: none;
	border: 1px solid #bbbbbb;
	border-radius: 10px;
	padding: 10px 14px;
	color: #222222;
}

.btn.primary {
	border-color: #2f6be2;
	background: #2f6be2;
	color: #ffffff;
}
</style>
