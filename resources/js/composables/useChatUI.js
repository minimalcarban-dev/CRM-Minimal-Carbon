import { computed, ref } from "vue";

export const useChatUI = ({ isMobile, viewportWidth, currentChannel }) => {
    const mobileSidebarOpen = ref(false);
    const threadPanelOpen = ref(false);
    const threadPanelWidth = ref(420);
    const infoPanelWidth = ref(320);
    const userInfoOpen = ref(true);
    const isResizingThread = ref(false);
    const isResizingInfo = ref(false);
    const resizeStartX = ref(0);
    const resizeStartWidth = ref(0);
    const resizeInfoStartX = ref(0);
    const resizeInfoStartWidth = ref(0);
    const openPanel = ref("info");

    const SIDEBAR_WIDTH = 320;
    const leftSidebarWidth = computed(() => {
        if (isMobile.value) return 0;
        if (viewportWidth.value <= 1024) return 280;
        if (viewportWidth.value <= 1200) return 300;
        return SIDEBAR_WIDTH;
    });

    const infoSidebarWidth = computed(() => {
        if (isMobile.value) {
            return threadPanelOpen.value
                ? viewportWidth.value
                : userInfoOpen.value
                    ? Math.min(380, Math.max(280, viewportWidth.value - 24))
                    : 0;
        }
        if (threadPanelOpen.value) return threadPanelWidth.value;
        return userInfoOpen.value ? Math.max(0, infoPanelWidth.value) : 0;
    });

    const setSidebarCssWidth = (width) => {
        if (typeof document === "undefined") return;
        document.documentElement.style.setProperty(
            "--info-sidebar-width",
            `${Math.max(0, width)}px`,
        );
        document.documentElement.style.setProperty(
            "--thread-sidebar-width",
            `${Math.max(0, width)}px`,
        );
        document.documentElement.style.setProperty(
            "--left-sidebar-width",
            `${Math.max(0, leftSidebarWidth.value)}px`,
        );
    };

    const chatMainStyle = computed(() => {
        if (isMobile.value) return { width: "100%" };
        return {
            width: "calc(100% - var(--left-sidebar-width, 320px) - var(--info-sidebar-width, 320px))",
        };
    });

    const infoSidebarStyle = computed(() => ({
        width: "var(--info-sidebar-width, 320px)",
    }));

    const openChannelList = () => {
        if (!isMobile.value) return;
        mobileSidebarOpen.value = true;
    };

    const closeChannelList = () => {
        mobileSidebarOpen.value = false;
    };

    return {
        mobileSidebarOpen,
        threadPanelOpen,
        threadPanelWidth,
        infoPanelWidth,
        userInfoOpen,
        isResizingThread,
        isResizingInfo,
        resizeStartX,
        resizeStartWidth,
        resizeInfoStartX,
        resizeInfoStartWidth,
        openPanel,
        leftSidebarWidth,
        infoSidebarWidth,
        chatMainStyle,
        infoSidebarStyle,
        setSidebarCssWidth,
        openChannelList,
        closeChannelList,
    };
};
