pipeline {
    agent any
    
    environment {
        // Credenciales de Docker Hub
        DOCKER_CREDS = credentials('docker-hub-creds')
        
        // Información de la imagen - CON TU USUARIO REAL
        DOCKER_IMAGE = 'jamescanos/php-simple-app'
        DOCKER_REGISTRY = 'https://registry.hub.docker.com'
        
        // Versión basada en el build number
        VERSION = "${env.BUILD_ID}"
    }
    
    stages {
        // Stage 1: Construir imagen Docker
        stage('Build Docker Image') {
            steps {
                echo 'Construyendo imagen Docker...'
                script {
                    // Verificar que tenemos los archivos correctos
                    sh '''
                        echo "=== Archivos en el workspace ==="
                        ls -la
                        echo "=== Verificando Dockerfile ==="
                        cat Dockerfile || echo "No hay Dockerfile"
                    '''
                    docker.build("${DOCKER_IMAGE}:${VERSION}")
                }
            }
        }
        
        // Stage 2: Probar la imagen
        stage('Test Image') {
            steps {
                echo 'Probando imagen Docker...'
                script {
                    def testContainer = docker.image("${DOCKER_IMAGE}:${VERSION}")
                    testContainer.inside {
                        sh '''
                            echo "=== Verificando PHP ==="
                            php --version
                            echo "=== Verificando Apache ==="
                            apache2 -v || httpd -v || echo "Servidor web no identificado"
                            echo "=== Verificando archivos de la aplicación ==="
                            find /var/www/html/ -type f -name "*.php" | head -10
                        '''
                    }
                }
            }
        }
        
        // Stage 3: Subir imagen a Docker Hub
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
        
        // Stage 4: Desplegar para pruebas
        stage('Deploy to Test') {
            steps {
                echo 'Desplegando aplicación de prueba...'
                script {
                    sh """
                    # Detener contenedor anterior si existe
                    docker stop test-php-app || true
                    docker rm test-php-app || true
                    
                    # Ejecutar nuevo contenedor
                    docker run -d \\
                        -p 8082:80 \\
                        --name test-php-app \\
                        ${DOCKER_IMAGE}:${VERSION}
                    """
                    
                    // Esperar que la aplicación esté lista
                    sleep 15
                    
                    // Probar que la aplicación responde
                    sh '''
                        echo "=== Probando aplicación ==="
                        curl -f http://localhost:8082/ && echo "✅ Aplicación funciona correctamente" || exit 1
                    '''
                }
            }
        }
    }
    
    post {
        always {
            echo '=== Limpieza final ==='
            // Limpiar contenedores de prueba
            sh 'docker stop test-php-app || true && docker rm test-php-app || true'
            cleanWs()
        }
        success {
            echo '🎉 ¡Pipeline ejecutado exitosamente!'
            echo "📦 Imagen Docker: ${DOCKER_IMAGE}:${VERSION}"
            echo "🐳 Disponible en Docker Hub: https://hub.docker.com/r/jamescanos/php-simple-app"
        }
        failure {
            echo '❌ Pipeline falló'
        }
    }
}