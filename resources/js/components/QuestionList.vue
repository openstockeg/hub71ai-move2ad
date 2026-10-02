<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    LoaderCircle,
    MessageCircleQuestion,
    Send,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import answers from '@/routes/answers';

const props = defineProps<{
    title: string;
    questions: string[];
    locale: 'en' | 'ar';
}>();

const labels = {
    en: {
        placeholder: 'Ask your own question about moving to Abu Dhabi…',
        ask: 'Ask',
    },
    ar: {
        placeholder: 'اطرح سؤالك عن الانتقال إلى أبوظبي…',
        ask: 'اسأل',
    },
} as const;

const t = computed(() => labels[props.locale]);
const page = usePage();

const own = ref('');
const opening = ref<string | null>(null);

// Every question opens a public answer page; the same question is answered once and shared.
// Answer → answer is the same page component: preserveState 'errors' gives the new page a fresh
// instance (or it would never start generating) while keeping the typed question on a validation error.
// One request at a time, so a double-click is not counted as two asks.
const open = (question: string) => {
    if (opening.value) {
        return;
    }

    router.post(
        answers.store().url,
        { question, locale: props.locale },
        {
            preserveState: 'errors',
            onStart: () => (opening.value = question),
            onFinish: () => (opening.value = null),
        },
    );
};
</script>

<template>
    <section>
        <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
            <MessageCircleQuestion class="size-5 text-brand" />{{ title }}
        </h2>
        <ul class="grid grid-cols-1 gap-2">
            <li v-for="question in questions" :key="question">
                <button
                    type="button"
                    :disabled="!!opening"
                    class="group flex w-full items-center gap-3 rounded-lg border bg-card px-4 py-3 text-start text-sm transition-colors hover:border-brand hover:bg-sand disabled:cursor-wait"
                    :class="{ 'opacity-60': opening && opening !== question }"
                    @click="open(question)"
                >
                    <span class="min-w-0 flex-1">{{ question }}</span>
                    <LoaderCircle
                        v-if="opening === question"
                        class="size-4 shrink-0 animate-spin text-brand"
                    />
                    <ArrowUpRight
                        v-else
                        class="size-4 shrink-0 text-muted-foreground group-hover:text-brand rtl:-scale-x-100"
                    />
                </button>
            </li>
        </ul>
        <form class="mt-3 flex gap-2" @submit.prevent="open(own)">
            <input
                v-model="own"
                type="text"
                required
                minlength="8"
                maxlength="300"
                :placeholder="t.placeholder"
                :aria-label="t.placeholder"
                class="min-w-0 flex-1 rounded-lg border bg-background px-4 py-3 text-sm outline-none focus:border-brand"
            />
            <button
                type="submit"
                :disabled="!!opening"
                class="flex shrink-0 items-center gap-1.5 rounded-lg bg-brand px-4 text-sm font-medium text-brand-foreground disabled:opacity-60"
            >
                <LoaderCircle
                    v-if="opening === own"
                    class="size-4 animate-spin"
                />
                <Send v-else class="size-4 rtl:-scale-x-100" />{{ t.ask }}
            </button>
        </form>
        <p
            v-if="page.props.errors.question"
            role="alert"
            class="mt-2 text-sm text-destructive"
        >
            {{ page.props.errors.question }}
        </p>
    </section>
</template>
