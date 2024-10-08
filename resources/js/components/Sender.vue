<template>
    <li class="flex flex-col mb-4 bg-pink-200 p-2 rounded-l-full transition-all message-line sender-message-line">
        <div class="mb-2 text-end text-teal-700 message-username">
            {{ message.sender.name }}
        </div>
        <div class="text-end text-sm message-text" v-html="message.content">
        </div>
        <div class="text-center mt-3 text-xs message-time">
            <i class="fas fa-solid fa-clock"></i>
            {{ message.time_ago }}
        </div>
    </li>
</template>

<script>
export default {
    name: "Sender",
    props: ["message"]
}
</script>

<style scoped>

</style>
