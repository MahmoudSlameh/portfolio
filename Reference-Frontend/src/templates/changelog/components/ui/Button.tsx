import { forwardRef, type ButtonHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost';
export type ButtonSize = 'sm' | 'md';

const variantClasses: Record<ButtonVariant, string> = {
  primary:
    'bg-signal text-on-signal font-semibold hover:-translate-y-0.5 hover:shadow-[0_14px_30px_-12px_var(--glow-lime)] active:translate-y-0 active:scale-[0.98]',
  secondary:
    'border border-line-strong text-ink hover:-translate-y-0.5 hover:border-electric hover:bg-electric/10 hover:text-electric active:translate-y-0 active:scale-[0.98]',
  ghost: 'text-ink-muted hover:text-ink hover:bg-surface active:scale-[0.98]',
};

const sizeClasses: Record<ButtonSize, string> = {
  sm: 'h-9 px-3.5 text-[0.8125rem] gap-1.5',
  md: 'h-11 px-5 text-sm gap-2',
};

export const buttonClasses = (variant: ButtonVariant = 'primary', size: ButtonSize = 'md'): string =>
  cn(
    'inline-flex shrink-0 items-center justify-center rounded-full font-medium whitespace-nowrap transition-[transform,background-color,border-color,color,box-shadow] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] disabled:pointer-events-none disabled:opacity-50',
    variantClasses[variant],
    sizeClasses[size],
  );

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button(
  { variant = 'primary', size = 'md', className, type = 'button', ...props },
  ref,
) {
  return <button ref={ref} type={type} className={cn(buttonClasses(variant, size), className)} {...props} />;
});
