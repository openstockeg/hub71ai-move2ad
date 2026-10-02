<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { BadgeCheck, ShieldAlert } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import checks from '@/routes/checks';

const props = defineProps<{ locale: 'en' | 'ar' }>();

// Federal Decree-Law 33/2021: the employer bears all recruitment costs (also in facts.json).
const EMPLOYER_PAYS_LAW = 'https://uaelegislation.gov.ae/en/legislations/1541';

const labels = {
    en: {
        title: 'Is this job offer real?',
        intro: 'Paste the WhatsApp message, email or LinkedIn offer you received. We check it against UAE labour law and official warnings, and show you every red flag with its source.',
        placeholder: 'Paste the job offer here…',
        example: 'Try an example',
        submit: 'Check this offer',
        privacy:
            'Remove your name and phone number before pasting. Free · no account needed',
        rule: 'In the UAE, the employer pays for your visa. Anyone asking you for a "visa fee" is breaking the law.',
        ruleSource: 'Federal Decree-Law 33/2021',
        sample: `Congratulations! You have been selected for the position of Senior Software Engineer at Al Noor Petroleum, Abu Dhabi. Salary AED 45,000/month + free accommodation and flight. No interview needed.
To process your work visa and medical, please pay a refundable visa processing fee of USD 350 within 24 hours via Western Union to our HR officer Mr. Khalid.
Contact: alnoor.hr.recruitment@gmail.com / WhatsApp +971 55 123 4567`,
    },
    ar: {
        title: 'هل عرض العمل هذا حقيقي؟',
        intro: 'الصق رسالة واتساب أو البريد الإلكتروني أو عرض لينكدإن الذي وصلك. نفحصه وفق قانون العمل الإماراتي والتحذيرات الرسمية، ونعرض لك كل علامة تحذير مع مصدرها.',
        placeholder: 'الصق عرض العمل هنا…',
        example: 'جرّب مثالًا',
        submit: 'افحص هذا العرض',
        privacy: 'احذف اسمك ورقم هاتفك قبل اللصق. مجاني · بدون حساب',
        rule: 'في الإمارات، صاحب العمل هو من يدفع تكاليف تأشيرتك. من يطلب منك "رسوم تأشيرة" يخالف القانون.',
        ruleSource: 'المرسوم بقانون اتحادي رقم 33 لسنة 2021',
        sample: `مبروك! تم اختيارك لوظيفة مهندس برمجيات أول في شركة النور للبترول – أبوظبي. الراتب 45,000 درهم شهريًا مع سكن وتذكرة طيران مجانية. لا حاجة لمقابلة.
لإتمام إجراءات تأشيرة العمل والفحص الطبي يرجى دفع رسوم معالجة تأشيرة مستردة بقيمة 350 دولارًا خلال 24 ساعة عبر ويسترن يونيون إلى مسؤول الموارد البشرية السيد خالد.
للتواصل: alnoor.hr.recruitment@gmail.com / واتساب ‎+971 55 123 4567`,
    },
} as const;

const t = computed(() => labels[props.locale]);
const dir = computed(() => (props.locale === 'ar' ? 'rtl' : 'ltr'));
const message = ref('');

// Autofocus only on desktop: on phones it pops the keyboard over the intro.
const isDesktop = window.matchMedia('(min-width: 768px)').matches;
</script>

<template>
    <Head :title="t.title" />

    <section class="bg-sand" :dir="dir" :lang="locale">
        <div
            class="mx-auto grid max-w-3xl grid-cols-1 gap-5 px-4 py-6 md:py-14"
        >
            <div class="flex items-center justify-between gap-3">
                <p
                    class="flex items-center gap-2 text-xs font-medium tracking-wide text-brand uppercase md:text-sm"
                >
                    <ShieldAlert class="size-4" /> Move2AD
                </p>
                <div class="flex gap-1 rounded-md border bg-card p-0.5 text-sm">
                    <Link
                        v-for="option in [
                            { value: 'en', label: 'English' },
                            { value: 'ar', label: 'العربية' },
                        ]"
                        :key="option.value"
                        :href="checks.create({ query: { lang: option.value } })"
                        preserve-state
                        replace
                        class="rounded px-2.5 py-1"
                        :class="
                            locale === option.value
                                ? 'bg-brand/10 font-medium text-brand'
                                : 'text-muted-foreground'
                        "
                        >{{ option.label }}</Link
                    >
                </div>
            </div>

            <div>
                <h1
                    class="text-3xl leading-tight font-semibold tracking-tight md:text-5xl"
                >
                    {{ t.title }}
                </h1>
                <p
                    class="mt-3 text-base text-muted-foreground md:mt-4 md:text-lg"
                >
                    {{ t.intro }}
                </p>
            </div>

            <Form
                v-bind="checks.store.form()"
                v-slot="{ errors, processing }"
                class="grid gap-3 rounded-xl border bg-card p-4 shadow-sm md:p-5"
            >
                <input type="hidden" name="locale" :value="locale" />
                <textarea
                    v-model="message"
                    name="message"
                    :aria-label="t.placeholder"
                    rows="8"
                    required
                    minlength="20"
                    maxlength="5000"
                    v-focus="isDesktop"
                    :placeholder="t.placeholder"
                    class="w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                />
                <InputError :message="errors.message" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-[auto_1fr]">
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        @click="message = t.sample"
                        >{{ t.example }}</Button
                    >
                    <Button
                        type="submit"
                        size="lg"
                        class="bg-brand text-brand-foreground hover:bg-brand/90"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        {{ t.submit }}
                    </Button>
                </div>
                <p class="text-center text-xs text-muted-foreground">
                    {{ t.privacy }}
                </p>
            </Form>

            <p
                class="flex items-start gap-2 rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm"
            >
                <ShieldAlert class="mt-0.5 size-4 shrink-0 text-destructive" />
                <span>
                    {{ t.rule }}
                    <a
                        :href="EMPLOYER_PAYS_LAW"
                        target="_blank"
                        rel="noopener"
                        class="mt-2 flex w-fit flex-wrap items-center gap-x-1 rounded-md bg-brand/10 px-2 py-0.5 text-xs text-brand"
                    >
                        <BadgeCheck class="size-3.5 shrink-0" />
                        <span>{{ t.ruleSource }}</span>
                        <span dir="ltr" class="opacity-70"
                            >· uaelegislation.gov.ae</span
                        >
                    </a>
                </span>
            </p>
        </div>
    </section>
</template>
