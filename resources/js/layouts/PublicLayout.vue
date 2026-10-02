<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ShieldAlert } from '@lucide/vue';
import { computed, watchEffect } from 'vue';
import { dashboard, home, login } from '@/routes';
import checks from '@/routes/checks';

const page = usePage<{
    locale?: 'en' | 'ar';
    brief?: { locale: 'en' | 'ar' };
}>();

// Pages with Arabic content flip the whole chrome (header, footer, scrollbar side) to RTL.
const locale = computed(
    () => page.props.locale ?? page.props.brief?.locale ?? 'en',
);

const labels = {
    en: {
        check: 'Check a job offer',
        journey: 'My journey',
        login: 'Log in',
        disclaimer:
            'Move2AD is an independent guide, not a government service. Answers are grounded in official UAE and Abu Dhabi sources — always confirm on the linked page before you act.',
    },
    ar: {
        check: 'افحص عرض عمل',
        journey: 'رحلتي',
        login: 'تسجيل الدخول',
        disclaimer:
            'Move2AD دليل مستقل وليس خدمة حكومية. تستند الإجابات إلى مصادر رسمية في الإمارات وأبوظبي — تأكد دائمًا من الصفحة المرتبطة قبل اتخاذ أي إجراء.',
    },
} as const;

const t = computed(() => labels[locale.value]);

watchEffect(() => {
    document.documentElement.lang = locale.value;
    document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr';
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col overflow-x-clip bg-background text-foreground"
    >
        <header class="border-b">
            <nav
                class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4"
            >
                <Link
                    :href="home()"
                    dir="ltr"
                    class="flex items-center gap-2 font-semibold"
                >
                    <span
                        class="flex size-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-brand-foreground"
                        >M2</span
                    >
                    Move2AD
                </Link>
                <div class="flex items-center gap-4 text-sm">
                    <Link
                        :href="checks.create({ query: { lang: locale } })"
                        class="flex items-center gap-1.5 font-medium text-brand hover:underline"
                        ><ShieldAlert class="size-4" />{{ t.check }}</Link
                    >
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="text-muted-foreground hover:text-foreground"
                        >{{ t.journey }}</Link
                    >
                    <Link
                        v-else
                        :href="login()"
                        class="text-muted-foreground hover:text-foreground"
                        >{{ t.login }}</Link
                    >
                </div>
            </nav>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t">
            <div
                class="mx-auto max-w-5xl px-4 py-6 text-xs text-muted-foreground"
            >
                {{ t.disclaimer }}
            </div>
        </footer>
    </div>
</template>
