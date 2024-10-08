<template>
    <div class="ring-1 ring-gray-400 p-1 m-2 emoji-list">
        <i class="fas fa-solid fa-times text-gray-500 text-xs cursor-pointer" @click="$emit('onClose')"></i>
        <ul >
            <li class="inline m-1 cursor-pointer hover:ring-1" v-for="(emoji, index) in emojiList" v-html="emoji" :key="index" @click="$emit('onSelect', emoji)"></li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "EmojiSelect",
    emits: ['onClose', 'onSelect'],
    data() {
        return {
            emojiList: [
                '&#128512;',
                '&#128513;',
                '&#128514;',
                '&#128515;',
                '&#128516;',
                '&#128517;',
                '&#128518;',
                '&#128519;',
                '&#128521;',
                '&#128522;',
                '&#128523;',
                '&#128524;',
                '&#128525;',
                '&#128526;',
                '&#128527;',
                '&#128528;',
                '&#128529;',
                '&#128530;',
                '&#128531;',
                '&#128532;',
            ]
        }
    }
}
</script>

<style scoped>

</style>
