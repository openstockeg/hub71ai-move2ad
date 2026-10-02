import type { Source } from '@/types/brief';

type Point = { title: string; detail: string; source_url: string | null };

export type AnswerContent = {
    on_topic: boolean;
    short_answer: { text: string; source_url: string | null };
    points: Point[];
    watch_out: Point | null;
    related_questions: string[];
};

export type Answer = {
    id: string;
    question: string;
    locale: 'en' | 'ar';
    status: 'pending' | 'generating' | 'ready' | 'failed';
    content: AnswerContent | null;
    sources: Source[];
    asked_count: number;
    updated_at: string;
};
