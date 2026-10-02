<script setup lang="ts">
import { BadgeCheck, ExternalLink } from '@lucide/vue';
import type { Source } from '@/types/brief';

defineProps<{ title: string; sources: Source[] }>();

// Readable path for the sources list, e.g. "/en/visas/golden-visa".
const sourcePath = (url: string) => {
    const { pathname } = new URL(url);

    return pathname === '/'
        ? ''
        : decodeURIComponent(pathname).replace(/\/$/, '');
};
</script>

<template>
    <section v-if="sources.length">
        <h2 class="mb-3 text-sm font-semibold text-muted-foreground uppercase">
            {{ title }}
        </h2>
        <ul class="grid grid-cols-1 gap-1 text-sm">
            <li v-for="source in sources" :key="source.url">
                <a
                    :href="source.url"
                    target="_blank"
                    rel="noopener"
                    class="group flex min-w-0 items-center gap-2 py-1.5"
                    :title="source.url"
                >
                    <BadgeCheck
                        v-if="source.official"
                        class="size-4 shrink-0 text-brand"
                    />
                    <ExternalLink
                        v-else
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <span dir="ltr" class="min-w-0 truncate">
                        <span class="font-medium group-hover:underline">{{
                            source.host
                        }}</span>
                        <span class="text-muted-foreground">{{
                            sourcePath(source.url)
                        }}</span>
                    </span>
                </a>
            </li>
        </ul>
    </section>
</template>
