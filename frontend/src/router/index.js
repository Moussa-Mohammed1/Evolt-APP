import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import StationsViews from '../views/StationsView.vue';
import UsersView from '../views/UsersView.vue';

const routes = [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView, meta: { hideNavbar: true } },
    { path: '/register', name: 'register', component: RegisterView, meta: { hideNavbar: true } },
    { path: '/stations', name: 'stations', component: StationsViews },
    { path: '/users', name: 'users', component: UsersView },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;