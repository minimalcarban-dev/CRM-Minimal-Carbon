import { ref } from "vue";

export const useChatMessages = () => {
    const messages = ref([]);
    const page = ref(1);
    const hasMoreMessages = ref(true);
    const loadingMessages = ref(false);

    const resetMessagePagination = () => {
        messages.value = [];
        page.value = 1;
        hasMoreMessages.value = true;
    };

    return {
        messages,
        page,
        hasMoreMessages,
        loadingMessages,
        resetMessagePagination,
    };
};
