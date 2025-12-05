<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});

const current = ref(0);
let interval: any = null;

function next() {
    if (!props.images.length) return;
    current.value = (current.value + 1) % props.images.length;
}

onMounted(() => {
    interval = setInterval(next, 4000);
});

onBeforeUnmount(() => {
    clearInterval(interval);
});
</script>


<template>
    <div v-if="props.images?.length" class="w-full md:h-[12rem] mt-4 select-none">

        <!-- CONTAINER -->
        <div class="
            relative
            w-full
            overflow-hidden
            rounded-2xl
            shadow-[0_8px_25px_rgba(0,0,0,0.12)]
            bg-white
        ">
            <!-- Slides -->
            <div
                class="flex transition-transform duration-500 ease-[cubic-bezier(.25,.8,.25,1)]"
                :style="{ transform: `translateX(-${current * 100}%)` }"
            >
                <div
                    v-for="banner in props.images"
                    :key="banner.id"
                    class="min-w-full h-44 md:h-[12rem] bg-cover bg-center"
                    :style="{ backgroundImage: `url('${banner.image_base64}')` }"
                ></div>
            </div>

            <!-- Indicadores -->
            <div class="absolute bottom-3 inset-x-0 flex justify-center gap-1.5">
                <span
                    v-for="(banner, index) in props.images"
                    :key="banner.id"
                    :class="[
                        'transition-all rounded-full',
                        current === index
                            ? 'w-4 h-1.5 bg-white shadow'
                            : 'w-1.5 h-1.5 bg-white/50'
                    ]"
                ></span>
            </div>
        </div>
    </div>
</template>
