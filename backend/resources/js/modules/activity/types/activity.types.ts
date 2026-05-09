export interface ActivityActor {
  id?: number;
  name?: string;
  email?: string;
}

export interface ActivityLogItem {
  id: string;
  user: ActivityActor | null;
  action: string;
  module: string;
  entity: string;
  description: string;
  status: 'success' | 'warning' | 'error';
  ip_address: string | null;
  created_at: string | null;
  meta: Record<string, unknown>;
}

export interface ActivityQuery {
  search: string;
  module: string;
  actionType: string;
  status: 'all' | 'success' | 'warning' | 'error';
  user: string;
  dateRange: 'all' | 'today' | '7d' | '30d';
  page: number;
  perPage: number;
}

export interface ActivityMetaPayload {
  current_user_permissions: string[];
}
