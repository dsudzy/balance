<template>
  <plaid 
    env="sandbox"
    :publicKey="publicKey"
    clientName="Test App"
    product="transactions"
    v-bind="{ onSuccess }">
    Open Plaid Link
  </plaid>
</template>

<script>
  import Plaid from './Plaid.vue';
  export default {
    name: 'PlaidLink',
    data() {
      return {
        // publicKey: process.env.VUE_APP_PLAID_PUBLIC_KEY,
        publicKey: '3fd48ca30df117a9ed536aa76bfb50'
      }
    },
    components: {
      Plaid
    },
    created() {
      console.log(this.publicKey)
    },
    methods: {
        onSuccess (token) {
            console.log(token)
        }
    }
  }
</script>