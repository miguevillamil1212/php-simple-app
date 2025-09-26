pipeline {
    agent any
    
    environment {
        // Credenciales de Docker Hub
        DOCKER_CREDS = credentials('docker-hub-creds')
        
        // Información de la imagen
        DOCKER_IMAGE = 'tu-usuario-docker/php-simple-app'
        DOCKER_REGISTRY = 'https://registry.hub.docker.com'
        
        // Versión basada en el build number
        VERSION = "${env.BUILD_ID}"
    }
    
    stages {
        // Stage 1: Obtener código del repositorio
        stage('Checkout') {
            steps {
                echo 'Obteniendo código del repositorio...'
                git branch: 'main', 
                    url: 'https://github.com/tu-usuario/php-simple-app.git'
            }
        }
        
        // Stage 2: Construir imagen Docker
        stage('Build Docker Image') {
            steps {
                echo 'Construyendo imagen Docker...'
                script {
                    docker.build("${DOCKER_IMAGE}:${VERSION}")
                }
            }
        }
        
        // Stage 3: Probar la imagen
        stage('Test Image') {
            steps {
                echo 'Probando imagen Docker...'
                script {
                    // Ejecutar tests básicos
                    def testContainer = docker.image("${DOCKER_IMAGE}:${VERSION}")
                    testContainer.inside {
                        sh '''
                            echo "=== Verificando PHP ==="
                            php --version
                            echo "=== Verificando Apache ==="
                            apache2 -v
                            echo "=== Verificando archivos ==="
                            ls -la /var/www/html/
                        '''
                    }
                }
            }
        }
        
        // Stage 4: Subir imagen a Docker Hub
        stage('Push to Docker Hub') {
            steps {
                echo 'Subiendo imagen a Docker Hub...'
                script {
                    docker.withRegistry("${DOCKER_REGISTRY}", 'docker-hub-creds') {
                        // Subir versión específica
                        docker.image("${DOCKER_IMAGE}:${VERSION}").push()
                        
                        // También subir como latest
                        docker.image("${DOCKER_IMAGE}:${VERSION}").push('latest')
                    }
                }
            }
        }
        
        // Stage 5: Desplegar para pruebas
        stage('Deploy to Test') {
            steps {
                echo 'Desplegando aplicación de prueba...'
                script {
                    sh """
                    # Detener contenedor anterior si existe
                    docker stop test-php-app || true
                    docker rm test-php-app || true
                    
                    # Ejecutar nuevo contenedor
                    docker run -d \
                        -p 8082:80 \
                        --name test-php-app \
                        -e BUILD_VERSION=${VERSION} \
                        ${DOCKER_IMAGE}:${VERSION}
                    """
                    
                    // Esperar que la aplicación esté lista
                    sleep 10
                    
                    // Probar que la aplicación responde
                    sh 'curl -f http://localhost:8082/ || exit 1'
                }
            }
        }
    }
    
    post {
        always {
            echo 'Pipeline ejecutado'
            // Limpiar workspace
            cleanWs()
        }
        success {
            echo '¡Despliegue exitoso!'
            echo "Imagen disponible en: ${DOCKER_IMAGE}:${VERSION}"
        }
        failure {
            echo 'Pipeline falló'
        }
    }
}