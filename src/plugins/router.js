import Vue from 'vue';
import VueRouter from 'vue-router';
import store from '@/plugins/store';

import AllCalendars from '@/components/AllCalendars'
import NotFound from '@/components/NotFound'
import EmptyLayout from '@/pages/EmptyLayout';

import MainIndex from '@/components/main/MainIndex'
import MainOU from '@/components/main/MainOU'
import MainClaimant from '@/components/main/MainClaimant'
import MainGroups from '@/components/main/MainGroups'
import MainGroup from '@/components/main/MainGroup'
import MainUsers from '@/components/main/MainUsers'
import MainUser from '@/components/main/MainUser'

import EpSubjects from "@/components/ep/EpSubjects";
import EpSubject from "@/components/ep/EpSubject";
import EpClasses from "@/components/ep/EpClasses";
import EpClass from "@/components/ep/EpClass";

import EpCalendar from "@/components/event/EpCalendar";
import EpCalendarTeacher from "@/components/event/EpCalendarTeacher";
import EpCalendarEditor from "@/components/event/EpCalendarEditor";

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [{
        path: '/',
        name: 'main',
        component: AllCalendars
    }, {
        path: '/login',
        name: 'main-login',
        component: () => import('@/views/main/x-signup.vue'),
        props: true
    }, {
        path: '/profile',
        name: 'main-profile',
        component: () => import('@/views/main/x-profile.vue'),
        props: true,
        meta: { requiresAuth: true }
    }, {
        path: '/event',
        name: 'event-layout',
        component: EmptyLayout,
        children: [{
            path: '',
            name: 'ep-event-teacher',
            component: EpCalendarTeacher,
            props: true,
            meta: { requiresAuth: true }
        }, {
            path: ':id',
            name: 'ep-event',
            component: EpCalendar,
        }, {
            path: 'edit/:id',
            name: 'ep-event-edit',
            component: EpCalendarEditor,
            meta: { requiresAuth: true }
        }]
    }, {
        path: '/ep',
        name: 'ep-layout',
        component: EmptyLayout,
        meta: { requiresAuth: true },
        children: [{
            path: 'subject',
            name: 'ep-subject-list',
            component: EmptyLayout,
            meta: { requiresAuth: true },
            children: [{
                path: '',
                name: 'ep-subject-list',
                meta: { requiresAuth: true },
                component: EpSubjects
            }, {
                path: 'new',
                name: 'ep-subject-new',
                meta: { requiresAuth: true },
                component: EpSubject
            }, {
                path: ':id',
                name: 'ep-subject-edit',
                meta: { requiresAuth: true },
                component: EpSubject
            }]
        }, {
            path: 'class',
            name: 'ep-class-list',
            component: EmptyLayout,
            meta: { requiresAuth: true },
            children: [{
                path: '',
                name: 'ep-class-list',
                meta: { requiresAuth: true },
                component: EpClasses
            }, {
                path: 'new',
                name: 'ep-class-new',
                meta: { requiresAuth: true },
                component: EpClass
            }, {
                path: ':id',
                name: 'ep-class-edit',
                meta: { requiresAuth: true },
                component: EpClass
            }]
        }]
    }, {
        path: '/main',
        name: 'main-layout',
        component: EmptyLayout,
        meta: { requiresAuth: true },
        children: [{
            path: '',
            name: 'main-index',
            component: MainIndex
        }, {
            path: 'ou',
            name: 'main-ou',
            component: MainOU
        }, {
            path: 'claimant',
            name: 'main-claimant',
            component: MainClaimant
        }, {
            path: 'group',
            name: 'main-group-layout',
            component: EmptyLayout,
            children: [{
                path: '',
                name: 'main-group-list',
                component: MainGroups
            }, {
                path: 'new',
                name: 'main-group-new',
                component: MainGroup
            }, {
                path: ':code',
                name: 'main-group-edit',
                component: MainGroup
            }]
        }, {
            path: 'user',
            name: 'main-user-layout',
            component: EmptyLayout,
            children: [{
                path: '',
                name: 'main-user-list',
                component: MainUsers
            }, {
                path: 'new',
                name: 'main-user-new',
                component: MainUser
            }, {
                path: ':login',
                name: 'main-user-edit',
                component: MainUser
            }]
        }, {
            path: '*/*',
            redirect: { name: 'main-index' }
        }]
    }, {
        path: '*',
        name: 'notFound',
        component: NotFound
    }]
})//*/

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        console.log('routed authentication')
        if (store.getters['authentication/isAuthenticated'] !== false) {
            console.log('userauthenticated: ' + store.getters['authentication/isAuthenticated'])
            next()
        } else {
            console.log('pageuld be redirect')
            next({
                path: '/login',
                query: { redirect: to.fullPath }
            })
        }
    } else {
        next()
    }

})//*/

export default router;