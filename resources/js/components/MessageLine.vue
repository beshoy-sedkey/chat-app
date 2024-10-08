<template>
    <Sender :message="message" v-if="auth.id === message.sender_id" />
    <Receiver :message="message" v-else />
</template>

<script>
import {inject} from "vue";

import Sender from "@/chat/components/Sender.vue";
import Receiver from "@/chat/components/Receiver.vue";

export default {
    name: "MessageLine",
    components: {Receiver, Sender},
    props: ["message"],
    setup() {
        const auth = inject("auth");

        return {
            auth
        }
    }
}
</script>

<style scoped>

</style>
