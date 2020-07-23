export default {
    state: {
        user: '',
        token: '',
        until: '',
    },
    getters: {
        user: state => state.user,
        token: state => state.token,
        until: state => state.until,
    },
    mutations: {
        setUser: (state, user) => state.user = user,
        setToken: (state, token) => state.token = token,
        setUntil: (state, until) => state.until = until,
    },
    actions: {
        changeUser: ({commit}, user) => commit('setUser', user),
        changeToken: ({commit}, token) => commit('setToken', token),
        changeUntil: ({commit}, until) => commit('setUntil', until),
    }
}
