import Vue from 'vue'
import VueRouter from 'vue-router'
import List from '../views/List.vue'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'list',
        component: List
    },
    {
        path: '/document/:id',
        name: 'item',
        component: function () {
            return import(/* webpackChunkName: "item" */ '../views/Item.vue')
        }
    },
    {
        path: '/login',
        name: 'login',
        component: function () {
            return import(/* webpackChunkName: "login" */ '../views/Login.vue')
        }
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

export default router
