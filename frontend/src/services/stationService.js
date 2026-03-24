import api from '../api/axios'

export const getStations = () => api.get('/stations')