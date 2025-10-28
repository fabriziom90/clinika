import { defineStore } from 'pinia';

export const useConfigStore = defineStore('config', {
  state: () => ({
    apiBaseUrl: 'http://127.0.0.1:3000', 
  }),
})