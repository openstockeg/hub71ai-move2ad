<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowUpRight, MessageCircleQuestion, Send } from '@lucide/vue';
import { computed } from 'vue';
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

// Every question opens a public answer page; the same question is answered once and shared.
// Answer → answer is the same page component: without a fresh instance the new page would never start generating.
const form = useForm({ question: '', locale: props.locale });
const ask = () =>
    form.post(answers.store().url, { preserveState: 'errors' });
</script>

<template>
    <section>
        <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
            <MessageCircleQuestion class="size-5 text-brand" />{{ title }}
        </h2>
        <ul class="grid grid-cols-1 gap-2">
            <li v-for="question in questions" :key="question">
                <Link
                    :href="answers.store()"
                    :data="{ question, locale }"
                    preserve-state="errors"
                    as="button"
                    class="group flex w-full items-center gap-3 rounded-lg border bg-card px-4 py-3 text-start text-sm transition-colors hover:border-brand hover:bg-sand"
                >
                    <span class="min-w-0 flex-1">{{ question }}</span>
                    <ArrowUpRight
                        class="size-4 shrink-0 text-muted-foreground group-hover:text-brand rtl:-scale-x-100"
                    />
                </Link>
            </li>
        </ul>
        <form class="mt-3 flex gap-2" @submit.prevent="ask">
            <input
                v-model="form.question"
                type="text"
                required
                minlength="8"
                maxlength="300"
                :placeholder="t.placeholder"
                class="min-w-0 flex-1 rounded-lg border bg-background px-4 py-3 text-sm outline-none focus:border-brand"
            />
            <button
                type="submit"
                :disabled="form.processing"
                class="flex shrink-0 items-center gap-1.5 rounded-lg bg-brand px-4 text-sm font-medium text-brand-foreground disabled:opacity-60"
            >
                <Send class="size-4 rtl:-scale-x-100" />{{ t.ask }}
            </button>
        </form>
        <p v-if="form.errors.question" class="mt-2 text-sm text-destructive">
            {{ form.errors.question }}
        </p>
    </section>
</template>
