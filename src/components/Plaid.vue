<template>
    <button class="plaid-link-button" @click="handleOnClick">
        <slot />
    </button>
</template>

<script>
export default {
    name: 'PlaidLink',
    props: {
        plaidUrl: {
            type: String,
            default: 'https://cdn.plaid.com/link/v2/stable/link-initialize.js'
        },
        env: {
            type: String,
            default: 'sandbox'
        },
        institution: String,
        selectAccount: Boolean,
        token: String,
        product: {
            type: String,
            default: 'transactions'
        },
        clientName: String,
        publicKey: String,
        webhook: String,
        onSuccess: Function,
        onExit: Function,
        onEvent: Function
    },
    created () {
        this.loadScript(this.plaidUrl)
            .then(this.onScriptLoaded)
            .catch(this.onScriptError)
    },
    beforeDestroy () {
        if (window.linkHandler) {
            window.linkHandler.exit()
        }
    },
    methods: {
        onScriptError (error) {
            console.error('There was an issue loading the link-initialize.js script')
        },
        onScriptLoaded () {
            console.log(this.product);
            window.linkHandler = window.Plaid.create({
                clientName: this.clientName,
                env: this.env,
                // apiVersion: 'v2',
                key: this.publicKey,
                product: this.product,
                // selectAccount: this.selectAccount,
                // token: this.token,
                webhook: this.webhook,
                onExit: this.onExit,
                onEvent: this.onEvent,
                onSuccess: this.onSuccess

                // clientName: 'Plaid Walkthrough Demo',
                // env: 'sandbox',
                // // Replace with your public_key from the Dashboard
                // key: '[PUBLIC_KEY]',
                // product: ['transactions'],
                // // Optional, use webhooks to get transaction and error updates
                // webhook: 'https://requestb.in',
            })
        },
        handleOnClick () {
            const institution = this.institution || null
            if (window.linkHandler) {
                window.linkHandler.open(institution)
            }
        },
        loadScript (src) {
            return new Promise(function (resolve, reject) {
                if (document.querySelector('script[src="' + src + '"]')) {
                    resolve()
                    return
                }

                const el = document.createElement('script')

                el.type = 'text/javascript'
                el.async = true
                el.src = src

                el.addEventListener('load', resolve)
                el.addEventListener('error', reject)
                el.addEventListener('abort', reject)

                document.head.appendChild(el)
            })
        }
    }
}
</script>