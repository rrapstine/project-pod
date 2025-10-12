const routes = {
  framework: {
    csrf: '/sanctum/csrf-cookie',
  },
  auth: {
    login: '/auth/login',
    logout: '/auth/logout',
    register: '/auth/register',
  },
  api: {
    user: {
      show: '/user',
      update: '/user',
      destroy: '/user',
    },
    workspaces: {
      index: '/workspaces',
      store: '/workspaces',
      show: (id: number) => `/workspaces/${id}`,
      update: (id: number) => `/workspaces/${id}`,
      destroy: (id: number) => `/workspaces/${id}`,
    },
    projects: {
      indexByWorkspace: (workspaceId: number) => `/workspaces/${workspaceId}/projects`,
      storeInWorkspace: (workspaceId: number) => `/workspaces/${workspaceId}/projects`,

      index: '/projects',
      show: (id: number) => `/projects/${id}`,
      update: (id: number) => `/projects/${id}`,
      destroy: (id: number) => `/projects/${id}`,
    },
    tasks: {
      indexByProject: (projectId: number) => `/projects/${projectId}/tasks`,
      storeInProject: (projectId: number) => `/projects/${projectId}/tasks`,

      index: '/tasks',
      show: (id: number) => `/tasks/${id}`,
      update: (id: number) => `/tasks/${id}`,
      destroy: (id: number) => `/tasks/${id}`,
    },
  },
} as const;

export default routes;
