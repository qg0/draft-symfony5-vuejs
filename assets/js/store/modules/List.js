export default {
    state: {
        currentPage: 1,
        totalRows: Number.MAX_SAFE_INTEGER
    },
    getters: {
        currentPage: state => state.currentPage,
        totalRows: state => state.totalRows,
    },
    mutations: {
        setPage: (state, currentPage) => state.currentPage = currentPage,
        setTotalRows: (state, totalRows) => state.totalRows = totalRows,
    },
    actions: {
        changePage: ({commit}, currentPage) => commit('setPage', currentPage),
        changeTotalRows: ({commit}, totalRows) => commit('setTotalRows', totalRows),
    }
}
