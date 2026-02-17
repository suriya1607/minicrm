
export const isRequired = (value: string, label: string) =>
  !value ? `${label} is required` : null;

export const minLength = (value: string, min: number, label: string) =>
  value.length < min ? `${label} must be at least ${min} characters` : null;

export const isValidEmail = (email: string) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!regex.test(email)) return "Invalid email format";
  return null;
};