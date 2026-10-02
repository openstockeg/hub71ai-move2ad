<script setup lang="ts">
import { BadgeCheck, ExternalLink } from "@lucide/vue";
import { computed } from "vue";
import type { Source } from "@/types/brief";

const props = defineProps<{
    url: string | null;
    sources: Source[];
}>();

const source = computed(() =>
    props.sources.find((source) => source.url === props.url),
);
</script>

<template>
    <a
        v-if="source"
        :href="source.url"
        target="_blank"
        rel="noopener"
        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs"
        :class="
            source.official
                ? 'bg-brand/10 text-brand'
                : 'bg-muted text-muted-foreground'
        "
        :title="source.url"
    >
        <BadgeCheck v-if="source.official" class="size-3.5" />
        <ExternalLink v-else class="size-3" />
        <span dir="ltr">{{ source.host }}</span>
    </a>
</template>
