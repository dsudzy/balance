<template>
  <div>
     <plaid
        env="sandbox"
        :publicKey="publicKey"
        clientName="Test App"
        product="transactions"
        :webhook='"https://requestb.in"'
        :onSuccess="onSuccess"
        :onExit="onExit"
        :onEvent="onEvent">
        Open Plaid Link
    </plaid>
  </div>
</template>

<script>
  import Plaid from './Plaid.vue';
  export default {
    name: 'PlaidLink',
    data() {
      return {
        publicKey: process.env.VUE_APP_PLAID_PUBLIC_KEY
      }
    },
    components: {
      Plaid
    },
    created() {
    },
    methods: {
      onSuccess (token, metadata) {
        console.log('success!');
        console.log(token);
        console.log(metadata);
      },
      onExit(e ,metadata) {
        // console.log('ere333r');
        console.log(e, metadata);
      },
      onEvent(e, metadata) {
        // console.log('erer566 ');
        // console.log(e);
        console.log(metadata);
      }
    }
  }
</script>