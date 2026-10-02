<script setup lang="ts">
import { Head, Link, router, useHttp, usePoll } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BadgeCheck,
    ListChecks,
    ShieldAlert,
    Sparkles,
    Users,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import QuestionList from '@/components/QuestionList.vue';
import SourceLink from '@/components/SourceLink.vue';
import SourcesList from '@/components/SourcesList.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { home } from '@/routes';
import answers from '@/routes/answers';
import checks from '@/routes/checks';
import type { Answer } from '@/types/answer';

const props = defineProps<{ answer: Answer }>();

const labels = {
    en: {
        loading: 'Checking official sources for this question…',
        loadingSteps: [
            'Searching official UAE and Abu Dhabi sites',
            'Cross-checking with our verified facts',
            'Writing a clear answer',
        ],
        failed: 'We could not finish this answer. Please try again.',
        retry: 'Try again',
        shortAnswer: 'Short answer',
        details: 'What you need to know',
        watchOut: 'Watch out',
        related: 'People also ask',
        sources: 'Sources',
        asked: (n: number) => (n === 1 ? 'Asked once' : `Asked ${n} times`),
        updated: 'Answered',
        ctaTitle: 'Want this for your own situation?',
        ctaText:
            'Get a free personal brief: visa routes, costs and next steps for your profession and family.',
        ctaBrief: 'Get my brief',
        ctaCheck: 'Check a job offer',
    },
    ar: {
        loading: 'نتحقق من المصادر الرسمية لهذا السؤال…',
        loadingSteps: [
            'البحث في المواقع الرسمية للإمارات وأبوظبي',
            'المقارنة بالحقائق التي تحققنا منها',
            'كتابة إجابة واضحة',
        ],
        failed: 'تعذّر إكمال الإجابة. يرجى المحاولة مرة أخرى.',
        retry: 'حاول مجددًا',
        shortAnswer: 'الإجابة المختصرة',
        details: 'ما تحتاج إلى معرفته',
        watchOut: 'انتبه',
        related: 'أسئلة يطرحها آخرون',
        sources: 'المصادر',
        asked: (n: number) => (n === 1 ? 'سُئل مرة واحدة' : `سُئل ${n} مرات`),
        updated: 'تاريخ الإجابة',
        ctaTitle: 'تريد إجابة تناسب وضعك أنت؟',
        ctaText:
            'احصل على ملخص شخصي مجاني: مسارات التأشيرة والتكاليف والخطوات التالية لمهنتك وعائلتك.',
        ctaBrief: 'احصل على ملخصي',
        ctaCheck: 'افحص عرض عمل',
    },
} as const;

const t = computed(() => labels[props.answer.locale]);
const c = computed(() => props.answer.content);
const dir = computed(() => (props.answer.locale === 'ar' ? 'rtl' : 'ltr'));
const answeredOn = computed(() =>
    new Date(props.answer.updated_at).toLocaleDateString(
        props.answer.locale === 'ar' ? 'ar-AE' : 'en-GB',
        { day: 'numeric', month: 'short', year: 'numeric' },
    ),
);

const retrying = ref(false);
const loading = computed(
    () =>
        retrying.value ||
        ['pending', 'generating'].includes(props.answer.status),
);

// Generate, then poll as a fallback (e.g. edge timeout or page refreshed mid-generation).
const http = useHttp(answers.generate(props.answer.id), {});
const run = () =>
    http
        .submit()
        .catch(() => {})
        .finally(() =>
            router.reload({
                only: ['answer'],
                onFinish: () => (retrying.value = false),
            }),
        );
onMounted(() => {
    if (props.answer.status === 'pending') {
        run();
    }
});

const retry = () => {
    retrying.value = true;
    loadingStep.value = 0;
    run();
};

const { start, stop } = usePoll(
    3000,
    { only: ['answer'] },
    { autoStart: false },
);

// Rotate loading messages while waiting.
const loadingStep = ref(0);
const ticker = setInterval(() => {
    loadingStep.value = Math.min(
        loadingStep.value + 1,
        t.value.loadingSteps.length - 1,
    );
}, 6000);
onUnmounted(() => clearInterval(ticker));

watch(loading, (isLoading) => (isLoading ? start() : stop()), {
    immediate: true,
});
</script>

<template>
    <Head :title="answer.question">
        <meta
            v-if="c"
            head-key="description"
            name="description"
            :content="c.short_answer.text"
        />
        <meta
            v-if="c && !c.on_topic"
            name="robots"
            content="noindex, nofollow"
        />
    </Head>

    <!-- grid-cols-1 (minmax(0, 1fr)) everywhere: long words/URLs must never widen the page. -->
    <div
        :dir="dir"
        :lang="answer.locale"
        class="mx-auto grid max-w-3xl grid-cols-1 gap-6 px-4 py-6 wrap-break-word md:gap-8 md:py-12"
    >
        <header>
            <h1
                class="text-2xl leading-snug font-semibold tracking-tight md:text-4xl md:leading-tight"
            >
                {{ answer.question }}
            </h1>
            <p
                class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
            >
                <span class="flex items-center gap-1.5"
                    ><Users class="size-4" />{{
                        t.asked(answer.asked_count)
                    }}</span
                >
                <span v-if="answer.status === 'ready'"
                    >{{ t.updated }} {{ answeredOn }}</span
                >
            </p>
        </header>

        <!-- Pending -->
        <div v-if="loading" class="grid grid-cols-1 gap-6">
            <div class="rounded-xl border bg-card p-6">
                <p class="flex items-center gap-2 font-medium">
                    <span class="relative flex size-3">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-brand opacity-60"
                        />
                        <span
                            class="relative inline-flex size-3 rounded-full bg-brand"
                        />
                    </span>
                    {{ t.loading }}
                </p>
                <ul class="mt-4 grid gap-2 text-sm">
                    <li
                        v-for="(step, i) in t.loadingSteps"
                        :key="step"
                        class="flex items-center gap-2 transition-opacity"
                        :class="i <= loadingStep ? 'opacity-100' : 'opacity-30'"
                    >
                        <BadgeCheck
                            class="size-4"
                            :class="
                                i < loadingStep
                                    ? 'text-brand'
                                    : 'text-muted-foreground'
                            "
                        />
                        {{ step }}
                    </li>
                </ul>
            </div>
            <Skeleton class="h-24 w-full" />
            <Skeleton class="h-40 w-full" />
        </div>

        <!-- Failed -->
        <div
            v-else-if="answer.status === 'failed' || !c"
            class="rounded-xl border border-destructive/40 bg-card p-6"
        >
            <p>{{ t.failed }}</p>
            <button
                type="button"
                class="mt-3 font-medium text-brand underline underline-offset-4"
                @click="retry"
            >
                {{ t.retry }}
            </button>
        </div>

        <!-- Ready -->
        <article v-else class="grid grid-cols-1 gap-8 md:gap-10">
            <section class="rounded-xl border-2 border-brand/40 bg-sand p-5">
                <h2
                    class="flex items-center gap-2 text-xs font-semibold tracking-wide text-brand uppercase"
                >
                    <Sparkles class="size-4" />{{ t.shortAnswer }}
                </h2>
                <p class="mt-2 text-base md:text-lg">
                    {{ c.short_answer.text }}
                </p>
                <div class="mt-3">
                    <SourceLink
                        :url="c.short_answer.source_url"
                        :sources="answer.sources"
                    />
                </div>
            </section>

            <section v-if="c.points.length">
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <ListChecks class="size-5 text-brand" />{{ t.details }}
                </h2>
                <div class="grid grid-cols-1 gap-3">
                    <div
                        v-for="point in c.points"
                        :key="point.title"
                        class="rounded-xl border bg-card p-4"
                    >
                        <h3 class="font-semibold">{{ point.title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ point.detail }}
                        </p>
                        <div class="mt-2">
                            <SourceLink
                                :url="point.source_url"
                                :sources="answer.sources"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section
                v-if="c.watch_out"
                class="rounded-xl border border-destructive/30 bg-destructive/5 p-4"
            >
                <h2 class="flex items-center gap-2 font-semibold">
                    <AlertTriangle class="size-5 shrink-0 text-destructive" />{{
                        t.watchOut
                    }}: {{ c.watch_out.title }}
                </h2>
                <p class="mt-1 text-sm">{{ c.watch_out.detail }}</p>
                <div class="mt-2">
                    <SourceLink
                        :url="c.watch_out.source_url"
                        :sources="answer.sources"
                    />
                </div>
            </section>

            <QuestionList
                :title="t.related"
                :questions="c.related_questions"
                :locale="answer.locale"
            />

            <aside class="rounded-xl border bg-card p-5">
                <h2 class="text-lg font-semibold">{{ t.ctaTitle }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t.ctaText }}
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <Link
                        :href="home()"
                        class="rounded-lg bg-brand px-4 py-2.5 text-sm font-medium text-brand-foreground"
                        >{{ t.ctaBrief }}</Link
                    >
                    <Link
                        :href="
                            checks.create({ query: { lang: answer.locale } })
                        "
                        class="flex items-center gap-1.5 rounded-lg border px-4 py-2.5 text-sm font-medium"
                        ><ShieldAlert class="size-4 text-brand" />{{
                            t.ctaCheck
                        }}</Link
                    >
                </div>
            </aside>

            <SourcesList :title="t.sources" :sources="answer.sources" />
        </article>
    </div>
</template>
