import { computed, ref } from 'vue';

export function usePerformance(sectionCount: number) {
    const leaderIndex = ref(0);
    const selectedIndex = ref(0);
    const following = ref(true);
    const activeIndex = computed(() =>
        following.value ? leaderIndex.value : selectedIndex.value,
    );

    function navigate(index: number) {
        if (!Number.isInteger(index) || index < 0 || index >= sectionCount) {
            return;
        }

        selectedIndex.value = index;
        following.value = false;
    }

    function setFollowing(value: boolean) {
        selectedIndex.value = activeIndex.value;
        following.value = value;
    }

    function advanceLeader() {
        if (leaderIndex.value < sectionCount - 1) {
            leaderIndex.value += 1;
        }
    }

    return {
        leaderIndex,
        activeIndex,
        following,
        navigate,
        setFollowing,
        advanceLeader,
    };
}
