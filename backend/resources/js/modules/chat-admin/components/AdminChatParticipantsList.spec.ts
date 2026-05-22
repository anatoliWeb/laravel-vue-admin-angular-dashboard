import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import AdminChatParticipantsList from './AdminChatParticipantsList.vue';

const globalStubs = {
  BaseLoader: { template: '<div data-testid="loader">loader</div>', props: ['label'] },
  BaseErrorState: { template: '<div data-testid="error">{{ title }} {{ description }}</div>', props: ['title', 'description'] },
  BaseEmptyState: { template: '<div data-testid="empty">{{ title }} {{ description }}</div>', props: ['title', 'description'] },
};

describe('AdminChatParticipantsList', () => {
  it('renders participant safe fields', () => {
    const wrapper = mount(AdminChatParticipantsList, {
      props: {
        loading: false,
        error: '',
        items: [
          {
            user_id: 42,
            name: 'Alice',
            role: 'admin',
            status: 'active',
            access_state: 'full',
          },
        ],
      },
      global: { stubs: globalStubs },
    });

    const text = wrapper.text();
    expect(text).toContain('Alice');
    expect(text).toContain('#42');
    expect(text).toContain('admin');
    expect(text).toContain('active');
    expect(text).toContain('full');
  });

  it('renders loading/empty states and hides sensitive fields', async () => {
    const wrapper = mount(AdminChatParticipantsList, {
      props: {
        loading: true,
        error: '',
        items: [],
      },
      global: { stubs: globalStubs },
    });

    expect(wrapper.find('[data-testid="loader"]').exists()).toBe(true);

    await wrapper.setProps({
      loading: false,
      items: [
        {
          user_id: 50,
          name: 'Bob',
          role: 'member',
          status: 'blocked',
          access_state: 'hidden',
          blocked_reason: 'sensitive',
          metadata: { internal_permission: 'chat.admin.moderate' },
        },
      ],
    });

    const text = wrapper.text();
    expect(text).toContain('Bob');
    expect(text).not.toContain('blocked_reason');
    expect(text).not.toContain('sensitive');
    expect(text).not.toContain('internal_permission');
    expect(text).not.toContain('chat.admin.moderate');

    await wrapper.setProps({
      items: [],
    });
    expect(wrapper.find('[data-testid="empty"]').exists()).toBe(true);
  });
});

