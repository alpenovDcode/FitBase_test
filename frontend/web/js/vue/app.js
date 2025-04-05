// Основная конфигурация Axios для работы с API
axios.defaults.baseURL = window.location.origin;
// Если определен window.baseUrl, используем его
if (window.baseUrl) {
    // Используем baseUrl только если он не пустой
    if (window.baseUrl !== '') {
        axios.defaults.baseURL = window.location.origin + window.baseUrl;
    }
}
console.log('Axios baseURL:', axios.defaults.baseURL);
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Добавляем отладочный вывод для запросов
axios.interceptors.request.use(
    config => {
        console.log('Request:', config.method.toUpperCase(), config.url, config.data);
        return config;
    }, 
    error => {
        console.error('Request Error:', error);
        return Promise.reject(error);
    }
);

axios.interceptors.response.use(
    response => {
        console.log('Response:', response.status, response.data);
        return response;
    },
    error => {
        console.error('Response Error:', error.response ? error.response.data : error.message);
        return Promise.reject(error);
    }
);

// Настраиваем делимитеры Vue, чтобы избежать конфликтов с Yii
Vue.config.delimiters = ['${', '}'];
Vue.config.unsafeDelimiters = ['{!!', '!!}'];

// Конфигурация Vue приложения
new Vue({
    el: '#app',
    data: {
        // Состояние авторизации
        isLoggedIn: false,
        token: localStorage.getItem('token'),
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        
        // Текущее представление
        currentView: 'login',
        
        // Формы
        loginForm: {
            username: '',
            password: ''
        },
        
        // Данные клубов
        clubs: [],
        clubForm: {
            name: '',
            address: '',
            phone: '',
            email: ''
        },
        showingClubForm: false,
        editingClub: null,
        
        // Данные клиентов
        clients: [],
        clientForm: {
            name: '',
            surname: '',
            phone: '',
            email: '',
            birth_date: '',
            gender: 'M',
            club_id: ''
        },
        showingClientForm: false,
        editingClient: null,
        
        // Статистика
        clientStats: null,
        clubStats: null,
        
        // Состояние интерфейса
        loading: false,
        notification: null
    },
    
    // Хуки жизненного цикла
    created() {
        // Проверка авторизации и инициализация
        if (this.token) {
            this.isLoggedIn = true;
            this.setupAxiosInterceptors();
            this.currentView = 'clubs';
            this.loadClubs();
        }
    },
    
    methods: {
        // Настройка перехватчиков запросов Axios
        setupAxiosInterceptors() {
            // Добавляем токен к каждому запросу
            axios.interceptors.request.use(config => {
                if (this.token) {
                    config.headers['Authorization'] = `Bearer ${this.token}`;
                }
                return config;
            });
            
            // Обработка ошибок авторизации
            axios.interceptors.response.use(
                response => response,
                error => {
                    if (error.response && error.response.status === 401) {
                        this.logout();
                        this.showNotification('Ошибка авторизации. Пожалуйста, войдите снова.', 'danger');
                    }
                    return Promise.reject(error);
                }
            );
        },
        
        // Вход в систему
        login() {
            this.loading = true;
            const credentials = {
                username: this.loginForm.username,
                password: this.loginForm.password
            };
            
            axios.post('/api/auth/login', credentials)
                .then(response => {
                    this.token = response.data.token;
                    this.user = response.data.user;
                    this.isLoggedIn = true;
                    
                    // Сохраняем данные в localStorage
                    localStorage.setItem('token', this.token);
                    localStorage.setItem('user', JSON.stringify(this.user));
                    
                    // Настраиваем Axios
                    this.setupAxiosInterceptors();
                    
                    // Переходим к списку клубов
                    this.currentView = 'clubs';
                    this.loadClubs();
                    
                    // Уведомление
                    this.showNotification('Вы успешно вошли в систему', 'success');
                })
                .catch(error => {
                    console.error('Ошибка входа:', error);
                    let errorMessage = 'Ошибка при входе в систему';
                    
                    if (error.response && error.response.data) {
                        if (error.response.data.message) {
                            errorMessage = error.response.data.message;
                        } else if (error.response.data.errors) {
                            const errors = error.response.data.errors;
                            errorMessage = Object.values(errors)[0][0] || errorMessage;
                        }
                    }
                    
                    this.showNotification(errorMessage, 'danger');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // Выход из системы
        logout() {
            // Удаляем токен из localStorage
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // Сбрасываем состояние приложения
            this.token = null;
            this.user = null;
            this.isLoggedIn = false;
            this.currentView = 'login';
            
            // Уведомление
            this.showNotification('Вы вышли из системы', 'info');
        },
        
        // Загрузка списка клубов
        loadClubs() {
            this.loading = true;
            
            axios.get('/api/club')
                .then(response => {
                    this.clubs = response.data;
                })
                .catch(error => {
                    console.error('Ошибка загрузки клубов:', error);
                    this.showNotification('Ошибка загрузки списка клубов', 'danger');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // Загрузка списка клиентов
        loadClients() {
            this.loading = true;
            
            axios.get('/api/client')
                .then(response => {
                    this.clients = response.data;
                })
                .catch(error => {
                    console.error('Ошибка загрузки клиентов:', error);
                    this.showNotification('Ошибка загрузки списка клиентов', 'danger');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // === КЛУБЫ ===
        
        // Отображение формы создания клуба
        showClubForm() {
            this.showingClubForm = true;
            this.editingClub = null;
            this.clubForm = {
                name: '',
                address: '',
                phone: '',
                email: ''
            };
        },
        
        // Отображение формы редактирования клуба
        viewClubDetails(club) {
            this.showingClubForm = true;
            this.editingClub = club;
            this.clubForm = { ...club };
        },
        
        // Отмена формы клуба
        cancelClubForm() {
            this.showingClubForm = false;
            this.editingClub = null;
        },
        
        // Сохранение клуба (создание или обновление)
        saveClub() {
            this.loading = true;
            
            const isUpdating = !!this.editingClub;
            // Use direct backend URL instead of frontend proxy
            const url = isUpdating ? 
                `/api/club/${this.editingClub.id}` : 
                '/api/club/create';
            const method = isUpdating ? 'put' : 'post';
            
            console.log('Saving club with URL:', url, 'method:', method, 'data:', this.clubForm);
            
            axios[method](url, this.clubForm)
                .then(response => {
                    this.showingClubForm = false;
                    this.loadClubs();
                    
                    const action = isUpdating ? 'обновлен' : 'создан';
                    this.showNotification(`Клуб успешно ${action}`, 'success');
                })
                .catch(error => {
                    console.error('Ошибка сохранения клуба:', error);
                    let errorMsg = 'Ошибка при сохранении клуба';
                    if (error.response && error.response.data && error.response.data.errors) {
                        errorMsg = Object.values(error.response.data.errors).join(', ');
                    }
                    this.showNotification(errorMsg, 'danger');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // Удаление клуба
        deleteClub(id) {
            if (confirm('Вы уверены, что хотите удалить этот клуб?')) {
                this.loading = true;
                
                axios.delete(`/api/club/${id}/delete`)
                    .then(response => {
                        this.loadClubs();
                        this.showNotification('Клуб успешно удален', 'success');
                    })
                    .catch(error => {
                        console.error('Ошибка удаления клуба:', error);
                        this.showNotification('Ошибка при удалении клуба', 'danger');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        },
        
        // === КЛИЕНТЫ ===
        
        // Отображение формы создания клиента
        showClientForm() {
            if (this.clubs.length === 0) {
                this.loadClubs();
            }
            
            this.showingClientForm = true;
            this.editingClient = null;
            this.clientForm = {
                name: '',
                surname: '',
                phone: '',
                email: '',
                birth_date: '',
                gender: 'M',
                club_id: ''
            };
        },
        
        // Отображение формы редактирования клиента
        viewClientDetails(client) {
            if (this.clubs.length === 0) {
                this.loadClubs();
            }
            
            this.showingClientForm = true;
            this.editingClient = client;
            this.clientForm = { ...client };
        },
        
        // Отмена формы клиента
        cancelClientForm() {
            this.showingClientForm = false;
            this.editingClient = null;
        },
        
        // Сохранение клиента (создание или обновление)
        saveClient() {
            this.loading = true;
            
            const isUpdating = !!this.editingClient;
            const url = isUpdating ? `/api/client/${this.editingClient.id}` : '/api/client/create';
            const method = isUpdating ? 'put' : 'post';
            
            axios[method](url, this.clientForm)
                .then(response => {
                    this.showingClientForm = false;
                    this.loadClients();
                    
                    const action = isUpdating ? 'обновлен' : 'создан';
                    this.showNotification(`Клиент успешно ${action}`, 'success');
                })
                .catch(error => {
                    console.error('Ошибка сохранения клиента:', error);
                    this.showNotification('Ошибка при сохранении клиента', 'danger');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // Удаление клиента
        deleteClient(id) {
            if (confirm('Вы уверены, что хотите удалить этого клиента?')) {
                this.loading = true;
                
                axios.delete(`/api/client/${id}/delete`)
                    .then(response => {
                        this.loadClients();
                        this.showNotification('Клиент успешно удален', 'success');
                    })
                    .catch(error => {
                        console.error('Ошибка удаления клиента:', error);
                        this.showNotification('Ошибка при удалении клиента', 'danger');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        },
        
        // === НАВИГАЦИЯ И ИНТЕРФЕЙС ===
        
        // Показ формы входа
        showLoginForm() {
            this.currentView = 'login';
        },
        
        // Смена представления
        setView(view) {
            this.currentView = view;
            
            if (view === 'clubs') {
                this.loadClubs();
            } else if (view === 'clients') {
                this.loadClients();
            } else if (view === 'statistics') {
                // Загрузка статистики
                this.loadStatistics();
            }
        },
        
        // Загрузка статистики
        loadStatistics() {
            this.loading = true;
            
            // Загрузка статистики по клиентам
            axios.get('/api/client/stats')
                .then(response => {
                    this.clientStats = response.data;
                })
                .catch(error => {
                    console.error('Ошибка загрузки статистики клиентов:', error);
                });
            
            // Загрузка статистики по клубам
            axios.get('/api/club/stats')
                .then(response => {
                    this.clubStats = response.data;
                })
                .catch(error => {
                    console.error('Ошибка загрузки статистики клубов:', error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        
        // Показ уведомления
        showNotification(message, type = 'info') {
            this.notification = { message, type };
            setTimeout(() => {
                this.notification = null;
            }, 3000);
        }
    },
    
    // Вычисляемые свойства
    computed: {
        // Список клубов для выбора в форме клиента
        activeClubs() {
            return this.clubs.filter(club => !club.deleted_at);
        }
    },
    
    // Наблюдатели
    watch: {
        // При изменении представления
        currentView(newView) {
            if (newView === 'clubs') {
                this.loadClubs();
            } else if (newView === 'clients') {
                this.loadClients();
            } else if (newView === 'statistics') {
                this.loadStatistics();
            }
        }
    }
}); 