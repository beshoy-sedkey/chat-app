<template>
    <div class="flex flex-col inline-block relative mx-2.5 w-80 z-99999 ring chat-panel">
        <header class="px-2 py-4 flex flex-row items-center bg-teal-700">
            <i class="fas fa-solid fa-times shrink mx-2 text-white cursor-pointer" title="close"
            @click="$emit('onCloseChat', user)"></i>
            <div class="flex-2 grow basis-1/2 text-white">{{ user.name }}</div>
        </header>
        <section class="px-2 py-4 h-72 overflow-y-scroll chat-panel-content" ref="chatContentRef"
                 @scroll="handleChatScroll">

            <i class="fas fa-circle-notch fa-spin absolute left-36 top-16 text-3xl" v-if="loading"></i>

            <ul>
                <MessageLine v-for="userMessage in userMessages" :key="userMessage.id" :message="userMessage" />
            </ul>

        </section>

        <EmojiSelect v-if="emojiBtnClicked" @onSelect="handleSelectEmoji" @onClose="emojiBtnClicked = false" />

        <footer class="flex flex-row items-center chat-panel-footer">
            <a href="#" @click.prevent="submitMessage" class="px-1 h-full bg-blue-700 text-white items-center flex">
                <i class="fas fa-solid fa-paper-plane mx-1"></i>
                Send
            </a>
            <button class="text-xl font-bold text-gray-600 mx-1" type="button" title="add emoji" @click="showEmojiList">&#128512;</button>
            <textarea name="currentMessage" class="grow p-2 border border-solid border-gray-300" v-model="messageContent"></textarea>
        </footer>
    </div>
</template>

<script>
import {ref, watch} from "vue";
import _ from "lodash";

import MessageLine from "@/chat/components/MessageLine.vue";
import EmojiSelect from "@/chat/components/EmojiSelect.vue";

export default {
    name: "ChatPanel",
    components: {EmojiSelect, MessageLine},
    props: ["user", "emittedMessage"],
    emits: ["onCloseChat"],
    setup(props) {

        const { user } = props;

        const chatContentRef = ref(null);

        const messageContent = ref("");
        const userMessages = ref([]);
        let scrollPoint = ref(0);
        const loading = ref(false);
        const emojiBtnClicked = ref(false);

        function handleSelectEmoji(emojiHtml) {
            sendMessage(user.id, emojiHtml);
        }

        function showEmojiList()
        {
            emojiBtnClicked.value = true;
        }

        function showLoading() {
            loading.value = true;
        }

        function hideLoading() {
            loading.value = false;
        }

        function submitMessage() {
            if(!messageContent.value) {
                return;
            }

            sendMessage(user.id, messageContent.value, () => {
                messageContent.value = "";
            });
        }

        function sendMessage(receiverId, messageContent, cb = null) {
            const payload = {
                receiver_id: receiverId,
                message_content: messageContent
            };

            window.axios.post("/messages", payload)
                .then(response => {
                    if(response && response.data.status) {
                        console.log(response);

                        // display and append the message in the message list
                        userMessages.value.push(response.data.message);

                        if(cb) {
                            cb();
                        }

                        // scroll bottom
                        scrollToChatBottom();
                    }
                }).catch(error => {
                console.error(error.response);
            });
        }

        async function getMessages() {
            showLoading();

            const result = await window.axios.get(`/messages?receiver_id=${user.id}`);
            hideLoading();

            if(result.data.messages) {
                userMessages.value = result.data.messages.reverse();

                scrollToChatBottom();
            }
        }

        function scrollToChatBottom()
        {
            setTimeout(() => {
                if(chatContentRef && chatContentRef.value) {
                    chatContentRef.value.scrollTop = chatContentRef.value.scrollHeight;

                    scrollPoint.value = chatContentRef.value.scrollTop;
                }
            }, 300);
        }

        const handleChatScroll = _.debounce((e) => {
                // if the user scrolls to top
                if(e.target.scrollTop - 50 < scrollPoint.value) {
                    showLoading();

                    const oldMessage = userMessages.value[0];

                    window.axios.get(`/messages?receiver_id=${user.id}&earlier_date=${oldMessage.created_at}`)
                        .then(response => {
                            if(response && response.data.messages) {
                                const filtered = [];

                                response.data.messages.reverse().forEach(message => {
                                    if(!userMessages.value.find(m => m.id == message.id)) {
                                        filtered.push(message);
                                    }
                                });

                                userMessages.value = [...filtered, ...userMessages.value];
                            }

                            setTimeout(() => {
                                hideLoading();
                            }, 2000);

                        }).catch(error => {
                            setTimeout(() => {
                                hideLoading();
                            }, 2000);

                        console.error(error.response);
                    });
                }

                scrollPoint.value = e.target.scrollTop;
        }, 1000);

        getMessages();

        watch(() => props.emittedMessage, (newMessage, oldMessage) => {
            if(newMessage) {
                const isMessageExist = userMessages.value.find(m => m.id == newMessage.id);

                if(!isMessageExist) {
                    userMessages.value.push(newMessage);
                    scrollToChatBottom();
                }
            }
        });

        return {
            messageContent,
            submitMessage,
            userMessages,
            chatContentRef,
            handleChatScroll,
            loading,
            emojiBtnClicked,
            showEmojiList,
            handleSelectEmoji
        }
    }
}
</script>

<style scoped>

</style>
