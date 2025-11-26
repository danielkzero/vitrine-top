<script setup lang="ts">
import { ref, onMounted } from "vue";

const props = defineProps({
    images: Array,
});

const current = ref(0);
let interval: any = null;

function next() {
    current.value = (current.value + 1) % props.images.length;
}

onMounted(() => {
    interval = setInterval(next, 4000);
});
</script>

<template>
    <div v-if="images?.length" class="w-full md:h-[500px] mt-4 select-none">

        <!-- CONTAINER DO CARDS -->
        <div class="
        relative 
        w-full 
        overflow-hidden 
        rounded-2xl
        shadow-[0_8px_25px_rgba(0,0,0,0.12)]
        bg-white
      ">
            <!-- Slides -->
            <div class="flex transition-transform duration-500 ease-[cubic-bezier(.25,.8,.25,1)]"
                :style="{ transform: `translateX(-${current * 100}%)` }">
                <div v-for="img in images" :key="img" class="
            min-w-full 
            h-44 md:h-[500px]
            bg-cover 
            bg-center
          " :style="{ backgroundImage: `url('${img}')` }"></div>
            </div>

            <!-- Indicadores estilo iPhone -->
            <div class="absolute bottom-3 inset-x-0 flex justify-center gap-1.5">
                <span v-for="(_, index) in images" :key="index" :class="[
                    'transition-all rounded-full',
                    current === index
                        ? 'w-4 h-1.5 bg-white shadow'
                        : 'w-1.5 h-1.5 bg-white/50'
                ]"></span>
            </div>
        </div>
    </div>
</template>
