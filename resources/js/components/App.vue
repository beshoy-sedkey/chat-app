<template>
    <div v-if="auth && auth.id !== undefined">
        <h2>Select any user to chat with</h2>
        <ul class="mt-3 mx-3 list-none inline-block">
            <li v-for="user in users" v-bind:key="user.id" class="mb-4 inline">
                <i class="fas fa-comment text-teal-700 px-2"></i>
                <a href="#" class="text-teal-700 hover:text-teal-900 underline" v-on:click.prevent="handleUserClick(user)">{{user.name}}</a>
                <span class="bg-red-500 text-white rounded-full px-2 absolute chat-badge-counter" v-if="user.unseen_messages.length > 0">
                    {{ user.unseen_messages.length }}
                </span>
            </li>
        </ul>
    </div>
    <div v-else>
        <p><a href="/login" class="text-teal-700 hover:text-teal-900 underline capitalize">Login to chat>>>></a></p>
    </div>

    <!-- Chat panel containers: wrapper for all chat panels -->
    <div class="fixed bottom-0 right-4 z-99999 w-full chat-panel-containers">
        <div class="relative overflow-x-scroll flex flex-row-reverse mx-6">
            <ChatPanel v-for="panel in chatPanels.panels" :key="panel.selectedUser.id"
               :user="panel.selectedUser" :emitted-message="panel.emittedMessage" @onCloseChat="hideChatPanel" />
        </div>
    </div>

    <audio style="display: none" id="chat-tone">
        <source :src="'/assets/sound/notification_tone.mp3'" type="audio/mpeg" />
        Your browser does not support audio element
    </audio>
</template>

<script>

import {provide, ref, reactive, onMounted} from "vue";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import ChatPanel from "@/chat/components/ChatPanel.vue";

export default {
    name: "App",
    components: {ChatPanel},
    props: ["auth"],
    setup({auth}) {

        provide('auth', auth);

        const onlineUsers = ref([]);

        // storing array of chat panels of clicked users
        // chat panel is an object of this form {selectedUser: null, emittedMessage: null}
        let chatPanels = reactive({panels: []});

        async function getOnlineUsers() {
            const result = await window.axios.get("/online-users");
            //  console.log(result);

            if(result.data.users) {
                onlineUsers.value = result.data.users;
            }
        }

        function showChatPanel(user, emittedMessage = null) {
            const isPanelOpened = chatPanels.panels.find(panel => panel.selectedUser.id === user.id);

            if(!isPanelOpened) {
                const userPanel = {
                    selectedUser: user,
                    emittedMessage
                };

                chatPanels.panels.push(userPanel);
                return true;
            }

            // if the panel already opened
            const index = chatPanels.panels.findIndex(panel => panel.selectedUser.id === user.id);

            chatPanels.panels[index] = {...chatPanels.panels[index], emittedMessage};
            return false;
        }

        function hideChatPanel(user) {
            const filtered = [...chatPanels.panels].filter(panel => panel.selectedUser.id !== user.id);

            chatPanels.panels = [...filtered];

            removeChatPanelFromStorage(user);
        }

        function updateSelectedUser(selectedUser)
        {
            const userIndex = onlineUsers.value.findIndex(u => u.id === selectedUser.id);

            if(userIndex !== -1) {
                onlineUsers.value[userIndex].unseen_messages = [];
            }
        }

        function handleUserClick(user)
        {
            showChatPanel(user);

            updateSelectedUser(user);

            persistChatPanelInStorage(user);
        }

        const displayToastMessage = (message) => {
            toast(message, {
                autoClose: 3000,
                type: "info",
                position: "top-left"
            });
        }

        const playChatTone = () => {
            (document.getElementById("chat-tone")).play();
        }

        const sendMessageUpdateRequest = (messageId) => {
            window.axios.put(`/messages/${messageId}`)
                .then(response => {
                    if(response.data.status) {
                        console.log('message updated');
                    }
                });
        }

        function persistChatPanelInStorage(user)
        {
            if(sessionStorage.getItem("opened_panels") && sessionStorage.getItem("opened_panels") !== "") {
                const opened = JSON.parse(sessionStorage.getItem("opened_panels"));
                if(!opened.find(p => p.id == user.id)) {
                    opened.push(user);
                    sessionStorage.setItem("opened_panels", JSON.stringify(opened));
                }
            } else {
                sessionStorage.setItem("opened_panels", JSON.stringify([user]));
            }
        }

        function removeChatPanelFromStorage(user)
        {
            if(sessionStorage.getItem("opened_panels")) {
                let opened = JSON.parse(sessionStorage.getItem("opened_panels"));
                opened = opened.filter(p => p.id != user.id);
                sessionStorage.setItem("opened_panels", JSON.stringify(opened));
            }
        }

        getOnlineUsers();

        onMounted(() => {
           if(auth) {

               // check for persisted chat panels and reopens it
               const opened = JSON.parse(sessionStorage.getItem("opened_panels"));
               if(opened) {
                   opened.forEach(p => {
                       showChatPanel(p);
                   });
               }

               window.Echo.private(`messages.${auth.id}`)
                    .listen('\\App\\Events\\MessageSent', e => {
                        const message = e.message;


                        showChatPanel(message.sender, message);

                        displayToastMessage(message.content);

                        playChatTone();

                        sendMessageUpdateRequest(message.id);
                    });
           }
        });

        return {
            users: onlineUsers,
            showChatPanel,
            hideChatPanel,
            chatPanels,
            handleUserClick
        }
    }
}
</script>

<style scoped>

</style>
