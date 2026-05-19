export interface NotificationPreview {
  id: string;
  type: 'info' | 'success' | 'warning' | 'error';
  title: string | null;
  message: string | null;
  createdAt: string | null;
  read: boolean;
}
